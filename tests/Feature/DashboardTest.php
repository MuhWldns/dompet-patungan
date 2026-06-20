<?php

use App\Models\Group;
use App\Models\Notification;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows app summaries for authenticated users', function () {
    $user = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $user->id,
        'name' => 'Dashboard Group',
        'invite_token' => fake()->uuid(),
    ]);
    $group->members()->attach($user->id, ['role' => 'admin']);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('expense creation creates member notifications', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Notify Group',
        'invite_token' => fake()->uuid(),
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member->id, ['role' => 'member']);

    $this
        ->actingAs($admin)
        ->post(route('groups.expenses.store', $group), [
            'title' => 'Dinner',
            'amount' => '100.00',
            'date' => '2026-06-21',
            'split_method' => 'equal',
        ]);

    expect(Notification::query()->where('user_id', $member->id)->where('type', 'bill.created')->exists())->toBeTrue();
});

test('users can mark notifications as read', function () {
    $user = User::factory()->create();
    $notification = Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'bill.created',
        'message' => 'New bill',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('notifications.read', $notification));

    $response->assertRedirect();
    expect($notification->refresh()->read_at)->not->toBeNull();
});
