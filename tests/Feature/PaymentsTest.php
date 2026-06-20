<?php

use App\Models\Expense;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createPaymentFixture(): array
{
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Payment Group',
        'invite_token' => fake()->uuid(),
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member->id, ['role' => 'member']);
    $expense = Expense::query()->create([
        'group_id' => $group->id,
        'payer_id' => $admin->id,
        'title' => 'Dinner',
        'amount' => '100.00',
        'date' => '2026-06-21',
    ]);
    $payment = Payment::query()->create([
        'expense_id' => $expense->id,
        'user_id' => $member->id,
        'amount' => '50.00',
        'status' => 'pending',
    ]);

    return [$group, $expense, $payment, $admin, $member];
}

test('members can view their bills', function () {
    [, , , , $member] = createPaymentFixture();

    $response = $this
        ->actingAs($member)
        ->get(route('payments.index'));

    $response->assertOk();
});

test('members can submit payments with proof uploads', function () {
    Storage::fake('public');
    [, , $payment, , $member] = createPaymentFixture();

    $response = $this
        ->actingAs($member)
        ->post(route('payments.pay', $payment), [
            'payment_method' => 'transfer',
            'proof' => UploadedFile::fake()->image('proof.jpg'),
        ]);

    $response->assertRedirect(route('payments.index'));
    $payment->refresh();
    expect($payment->status)->toBe('submitted');
    expect($payment->proof_path)->not->toBeNull();
    Storage::disk('public')->assertExists($payment->proof_path);
});

test('group admins can confirm submitted payments', function () {
    [, , $payment, $admin] = createPaymentFixture();
    $payment->update(['status' => 'submitted', 'payment_method' => 'transfer']);

    $response = $this
        ->actingAs($admin)
        ->patch(route('payments.confirm', $payment));

    $response->assertRedirect();
    expect($payment->refresh()->status)->toBe('confirmed');
});

test('group admins can reject submitted payments with a reason', function () {
    [, , $payment, $admin] = createPaymentFixture();
    $payment->update(['status' => 'submitted', 'payment_method' => 'transfer']);

    $response = $this
        ->actingAs($admin)
        ->patch(route('payments.reject', $payment), [
            'rejection_reason' => 'Proof is blurry',
        ]);

    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status)->toBe('rejected');
    expect($payment->rejection_reason)->toBe('Proof is blurry');
});

test('rejected payments can be resubmitted', function () {
    [, , $payment, , $member] = createPaymentFixture();
    $payment->update(['status' => 'rejected', 'rejection_reason' => 'Wrong proof']);

    $response = $this
        ->actingAs($member)
        ->post(route('payments.pay', $payment), [
            'payment_method' => 'qris',
        ]);

    $response->assertRedirect(route('payments.index'));
    $payment->refresh();
    expect($payment->status)->toBe('submitted');
    expect($payment->rejection_reason)->toBeNull();
});
