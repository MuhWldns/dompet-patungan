<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectPaymentRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Group;
use App\Models\Payment;
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

    public function pay(StorePaymentRequest $request, Payment $payment): RedirectResponse
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

        return redirect()->route('payments.index');
    }

    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizeAdmin($request, $payment);

        $payment->update([
            'status' => 'confirmed',
            'rejection_reason' => null,
        ]);

        return back();
    }

    public function reject(RejectPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->authorizeAdmin($request, $payment);

        $payment->update([
            'status' => 'rejected',
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        return back();
    }

    private function authorizeAdmin(Request $request, Payment $payment): void
    {
        $group = $payment->expense()->with('group')->firstOrFail()->group;

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
