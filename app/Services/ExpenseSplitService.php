<?php

namespace App\Services;

use App\Models\Group;
use Illuminate\Validation\ValidationException;

class ExpenseSplitService
{
    /**
     * @return array<int, string>
     */
    public function buildEqualSplits(Group $group, string $amount): array
    {
        $userIds = $group->members()->orderBy('users.id')->pluck('users.id')->all();
        $totalCents = $this->toCents($amount);
        $memberCount = count($userIds);

        if ($memberCount === 0) {
            throw ValidationException::withMessages([
                'splits' => 'Group must have at least one member.',
            ]);
        }

        $baseCents = intdiv($totalCents, $memberCount);
        $remainder = $totalCents % $memberCount;
        $splits = [];

        foreach ($userIds as $index => $userId) {
            $splits[$userId] = $this->fromCents($baseCents + ($index < $remainder ? 1 : 0));
        }

        return $splits;
    }

    /**
     * @param  array<int|string, string|int|float|null>  $input
     * @return array<int, string>
     */
    public function buildCustomSplits(Group $group, array $input, string $amount): array
    {
        $memberIds = $group->members()->pluck('users.id')->mapWithKeys(
            fn (int $id) => [$id => true],
        );
        $splits = [];
        $totalCents = 0;

        foreach ($input as $userId => $splitAmount) {
            $userId = (int) $userId;

            if (! $memberIds->has($userId)) {
                continue;
            }

            $cents = $this->toCents((string) $splitAmount);

            if ($cents > 0) {
                $splits[$userId] = $this->fromCents($cents);
                $totalCents += $cents;
            }
        }

        if ($totalCents !== $this->toCents($amount)) {
            throw ValidationException::withMessages([
                'splits' => 'Custom split total must match the expense amount.',
            ]);
        }

        return $splits;
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
