<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseSplit;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;

class SettlementService
{
    /**
     * @return list<array{from_user_id: int, from_name: string, to_user_id: int, to_name: string, amount: string}>
     */
    public function calculate(Group $group): array
    {
        $group->load(['members:id,name', 'expenses.splits.user:id,name', 'expenses.payments', 'expenses.payer:id,name']);
        $balances = [];
        $names = [];

        foreach ($group->members as $member) {
            /** @var User $member */
            $balances[$member->id] = 0;
            $names[$member->id] = $member->name;
        }

        foreach ($group->expenses as $expense) {
            /** @var Expense $expense */
            $balances[$expense->payer_id] += $this->toCents((string) $expense->amount);

            foreach ($expense->splits as $split) {
                /** @var ExpenseSplit $split */
                $balances[$split->user_id] -= $this->toCents((string) $split->amount);
            }

            foreach ($expense->payments as $payment) {
                /** @var Payment $payment */
                if ($payment->status !== 'confirmed') {
                    continue;
                }

                $balances[$payment->user_id] += $this->toCents((string) $payment->amount);
                $balances[$expense->payer_id] -= $this->toCents((string) $payment->amount);
            }
        }

        $debtors = [];
        $creditors = [];

        foreach ($balances as $userId => $balance) {
            if ($balance < 0) {
                $debtors[] = ['user_id' => $userId, 'amount' => abs($balance)];
            }

            if ($balance > 0) {
                $creditors[] = ['user_id' => $userId, 'amount' => $balance];
            }
        }

        $transfers = [];
        $debtorIndex = 0;
        $creditorIndex = 0;

        while (isset($debtors[$debtorIndex], $creditors[$creditorIndex])) {
            $amount = min($debtors[$debtorIndex]['amount'], $creditors[$creditorIndex]['amount']);
            $fromUserId = $debtors[$debtorIndex]['user_id'];
            $toUserId = $creditors[$creditorIndex]['user_id'];

            $transfers[] = [
                'from_user_id' => $fromUserId,
                'from_name' => $names[$fromUserId],
                'to_user_id' => $toUserId,
                'to_name' => $names[$toUserId],
                'amount' => $this->fromCents($amount),
            ];

            $debtors[$debtorIndex]['amount'] -= $amount;
            $creditors[$creditorIndex]['amount'] -= $amount;

            if ($debtors[$debtorIndex]['amount'] === 0) {
                $debtorIndex++;
            }

            if ($creditors[$creditorIndex]['amount'] === 0) {
                $creditorIndex++;
            }
        }

        return $transfers;
    }

    private function toCents(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
