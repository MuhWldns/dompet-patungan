<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectPaymentRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Group;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $payments = Payment::query()
            ->with(['expense.group'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return Inertia::render('payments/Index', [
            'payments' => $payments,
        ]);
    }

    public function pay(StorePaymentRequest $request, Payment $payment, NotificationService $notificationService): RedirectResponse
    {
        abort_unless($payment->user_id === $request->user()->id, 403);
        abort_unless(in_array($payment->status, ['pending', 'rejected'], true), 403);

        $proofPath = $request->file('proof')?->store('payment-proofs', 'public');

        $payment->update([
            'payment_method' => $request->validated('payment_method'),
            'proof_path' => $proofPath ?? $payment->proof_path,
            'status' => 'submitted',
            'rejection_reason' => null,
            'paid_at' => now(),
        ]);

        $expense = $payment->expense()->firstOrFail();
        $payer = $expense->payer()->firstOrFail();
        $notificationService->send(
            $payer,
            'payment.submitted',
            "Pembayaran {$expense->title} menunggu konfirmasi.",
            route('groups.show', $expense->group_id),
        );

        return redirect()->route('payments.index');
    }

    public function confirm(Request $request, Payment $payment, NotificationService $notificationService): RedirectResponse
    {
        $this->authorizeAdmin($request, $payment);

        $payment->update([
            'status' => 'confirmed',
            'rejection_reason' => null,
        ]);

        $expense = $payment->expense()->firstOrFail();
        $user = $payment->user()->firstOrFail();
        $notificationService->send(
            $user,
            'payment.confirmed',
            "Pembayaran {$expense->title} sudah dikonfirmasi.",
            route('payments.index'),
        );

        return back();
    }

    public function reject(RejectPaymentRequest $request, Payment $payment, NotificationService $notificationService): RedirectResponse
    {
        $this->authorizeAdmin($request, $payment);

        $payment->update([
            'status' => 'rejected',
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        $expense = $payment->expense()->firstOrFail();
        $user = $payment->user()->firstOrFail();
        $notificationService->send(
            $user,
            'payment.rejected',
            "Pembayaran {$expense->title} ditolak.",
            route('payments.index'),
        );

        return back();
    }

    private function authorizeAdmin(Request $request, Payment $payment): void
    {
        $group = $payment->expense()->firstOrFail()->group()->firstOrFail();

        abort_unless($this->isAdmin($request, $group), 403);
    }

    private function isAdmin(Request $request, Group $group): bool
    {
        return $group->members()
            ->whereKey($request->user()->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }
}
