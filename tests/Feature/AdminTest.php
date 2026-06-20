<?php

use App\Models\Expense;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;

test('normal users cannot access system admin pages', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.users.index'));

    $response->assertForbidden();
});

test('system admins can access system admin pages', function () {
    $admin = User::factory()->create(['role' => 'system_admin']);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users.index'));

    $response->assertOk();
});

test('system admins can deactivate and reactivate users', function () {
    $admin = User::factory()->create(['role' => 'system_admin']);
    $user = User::factory()->create();

    $this
        ->actingAs($admin)
        ->patch(route('admin.users.status', $user), ['is_active' => false])
        ->assertRedirect(route('admin.users.index'));

    expect($user->refresh()->is_active)->toBeFalse();

    $this
        ->actingAs($admin)
        ->patch(route('admin.users.status', $user), ['is_active' => true])
        ->assertRedirect(route('admin.users.index'));

    expect($user->refresh()->is_active)->toBeTrue();
});

test('system admin stats are aggregate and omit private expense titles', function () {
    $admin = User::factory()->create(['role' => 'system_admin']);
    $owner = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Private Stats Group',
        'invite_token' => fake()->uuid(),
    ]);
    $expense = Expense::query()->create([
        'group_id' => $group->id,
        'payer_id' => $owner->id,
        'title' => 'Secret Dinner',
        'amount' => '80.00',
        'date' => '2026-06-21',
    ]);
    Payment::query()->create([
        'expense_id' => $expense->id,
        'user_id' => $owner->id,
        'amount' => '80.00',
        'status' => 'confirmed',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.stats.index'));

    $response->assertOk();
    $response->assertDontSee('Secret Dinner');
});
