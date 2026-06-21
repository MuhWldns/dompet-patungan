<?php

use App\Models\Notification;
use App\Models\User;

test('user can get unread notification count', function () {
    $user = User::factory()->create();
    Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'bill.created',
        'message' => 'Test notification 1',
    ]);
    Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'payment.submitted',
        'message' => 'Test notification 2',
    ]);
    Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'payment.confirmed',
        'message' => 'Test notification 3',
        'read_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson(route('notifications.unread-count'));

    $response->assertOk()->assertJson(['count' => 2]);
});

test('user can mark all notifications as read', function () {
    $user = User::factory()->create();
    Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'bill.created',
        'message' => 'Test 1',
    ]);
    Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'payment.submitted',
        'message' => 'Test 2',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('notifications.mark-all-read'));

    $response->assertRedirect();
    expect($user->appNotifications()->whereNull('read_at')->count())->toBe(0);
});

test('user can filter notifications by unread', function () {
    $user = User::factory()->create();
    Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'bill.created',
        'message' => 'Unread',
    ]);
    Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'payment.confirmed',
        'message' => 'Read',
        'read_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('notifications.index', ['filter' => 'unread']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('notifications.data', 1)
        ->where('notifications.data.0.message', 'Unread')
    );
});

test('user can filter notifications by type', function () {
    $user = User::factory()->create();
    Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'bill.created',
        'message' => 'Bill notification',
    ]);
    Notification::query()->create([
        'user_id' => $user->id,
        'type' => 'payment.submitted',
        'message' => 'Payment notification',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('notifications.index', ['filter' => 'bill.created']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('notifications.data', 1)
        ->where('notifications.data.0.message', 'Bill notification')
    );
});
