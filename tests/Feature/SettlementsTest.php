<?php

use App\Models\Expense;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;

function createSettlementFixture(): array
{
    $admin = User::factory()->create(['name' => 'Admin']);
    $memberA = User::factory()->create(['name' => 'Member A']);
    $memberB = User::factory()->create(['name' => 'Member B']);
    $outsider = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Settlement Group',
        'invite_token' => fake()->uuid(),
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($memberA->id, ['role' => 'member']);
    $group->members()->attach($memberB->id, ['role' => 'member']);
    $expense = Expense::query()->create([
        'group_id' => $group->id,
        'payer_id' => $admin->id,
        'title' => 'Villa',
        'amount' => '120.00',
        'date' => '2026-06-21',
    ]);

    foreach ([$admin, $memberA, $memberB] as $user) {
        $expense->splits()->create([
            'user_id' => $user->id,
            'amount' => '40.00',
        ]);
    }

    Payment::query()->create([
        'expense_id' => $expense->id,
        'user_id' => $memberA->id,
        'amount' => '40.00',
        'status' => 'confirmed',
        'payment_method' => 'transfer',
    ]);
    Payment::query()->create([
        'expense_id' => $expense->id,
        'user_id' => $memberB->id,
        'amount' => '40.00',
        'status' => 'pending',
    ]);

    return [$group, $admin, $memberA, $memberB, $outsider];
}

test('group admins can generate minimal settlements', function () {
    [$group, $admin, , $memberB] = createSettlementFixture();

    $response = $this
        ->actingAs($admin)
        ->post(route('settlements.generate', $group));

    $response->assertRedirect(route('settlements.show', $group));
    $settlement = $group->settlements()->firstOrFail();

    expect($settlement->debt_details)->toHaveCount(1);
    expect($settlement->debt_details[0])->toMatchArray([
        'from_user_id' => $memberB->id,
        'from_name' => $memberB->name,
        'to_user_id' => $admin->id,
        'to_name' => $admin->name,
        'amount' => '40.00',
    ]);
});

test('non admin members cannot generate settlements', function () {
    [$group, , $memberA] = createSettlementFixture();

    $response = $this
        ->actingAs($memberA)
        ->post(route('settlements.generate', $group));

    $response->assertForbidden();
});

test('group members can view settlements', function () {
    [$group, $admin, $memberA] = createSettlementFixture();
    $this->actingAs($admin)->post(route('settlements.generate', $group));

    $response = $this
        ->actingAs($memberA)
        ->get(route('settlements.show', $group));

    $response->assertOk();
});

test('non members cannot view settlements', function () {
    [$group, , , , $outsider] = createSettlementFixture();

    $response = $this
        ->actingAs($outsider)
        ->get(route('settlements.show', $group));

    $response->assertForbidden();
});
