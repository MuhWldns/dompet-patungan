<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RemindOverduePayments extends Command
{
    protected $signature = 'notifications:remind-overdue';

    protected $description = 'Send overdue payment reminders for payments pending over 24 hours';

    public function handle(): int
    {
        $overdueThreshold = Carbon::now()->subHours(24);

        $overduePayments = Payment::query()
            ->where('status', 'pending')
            ->where('created_at', '<', $overdueThreshold)
            ->with('user', 'expense')
            ->get();

        $sentCount = 0;

        foreach ($overduePayments as $payment) {
            $existingNotification = Notification::query()
                ->where('user_id', $payment->user_id)
                ->where('type', 'payment.overdue')
                ->whereNull('read_at')
                ->where('message', 'like', "%{$payment->expense->title}%")
                ->exists();

            if ($existingNotification) {
                continue;
            }

            Notification::query()->create([
                'user_id' => $payment->user_id,
                'type' => 'payment.overdue',
                'message' => "Tagihan {$payment->expense->title} belum dibayar",
                'link' => route('payments.index'),
            ]);

            $sentCount++;
        }

        $this->info("Sent {$sentCount} overdue payment reminders.");

        return self::SUCCESS;
    }
}
