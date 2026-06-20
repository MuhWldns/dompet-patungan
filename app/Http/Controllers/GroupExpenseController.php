<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use App\Services\ExpenseSplitService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class GroupExpenseController extends Controller
{
    public function store(
        StoreExpenseRequest $request,
        Group $group,
        ExpenseSplitService $splitService,
        NotificationService $notificationService,
    ): RedirectResponse {
        abort_unless($this->isAdmin($request, $group), 403);

        $validated = $request->validated();
        $receiptPath = $request->file('receipt')?->store('receipts', 'public');

        DB::transaction(function () use ($group, $request, $validated, $receiptPath, $splitService, $notificationService): void {
            $expense = Expense::query()->create([
                'group_id' => $group->id,
                'payer_id' => $request->user()->id,
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'category' => $validated['category'] ?? null,
                'date' => $validated['date'],
                'receipt_path' => $receiptPath,
                'status' => 'pending',
            ]);

            $splits = $validated['split_method'] === 'equal'
                ? $splitService->buildEqualSplits($group, (string) $validated['amount'])
                : $splitService->buildCustomSplits($group, $validated['splits'] ?? [], (string) $validated['amount']);

            foreach ($splits as $userId => $amount) {
                $expense->splits()->create([
                    'user_id' => $userId,
                    'amount' => $amount,
                ]);

                $expense->payments()->create([
                    'user_id' => $userId,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);

                if ($userId !== $request->user()->id) {
                    $member = User::query()->find($userId);

                    if ($member !== null) {
                        $notificationService->send(
                            $member,
                            'bill.created',
                            "Tagihan baru {$expense->title} sebesar Rp {$amount}",
                            route('payments.index'),
                        );
                    }
                }
            }
        });

        return redirect()->route('groups.show', $group);
    }

    private function isAdmin(StoreExpenseRequest $request, Group $group): bool
    {
        return $group->members()
            ->whereKey($request->user()->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }
}
