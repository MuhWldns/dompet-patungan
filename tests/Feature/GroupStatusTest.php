<?php

use App\Models\Group;
use App\Models\User;

test('admin can change group status to settled', function () {
    $admin = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
        'status' => 'active',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);

    $response = $this
        ->actingAs($admin)
        ->patch(route('groups.status.update', $group), ['status' => 'settled']);

    $response->assertRedirect();
    expect($group->fresh()->status)->toBe('settled');
});

test('admin can change group status to closed', function () {
    $admin = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
        'status' => 'active',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);

    $response = $this
        ->actingAs($admin)
        ->patch(route('groups.status.update', $group), ['status' => 'closed']);

    $response->assertRedirect();
    expect($group->fresh()->status)->toBe('closed');
});

test('non-admin cannot change group status', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
        'status' => 'active',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member->id, ['role' => 'member']);

    $response = $this
        ->actingAs($member)
        ->patch(route('groups.status.update', $group), ['status' => 'settled']);

    $response->assertForbidden();
    expect($group->fresh()->status)->toBe('active');
});

test('cannot reopen a closed group', function () {
    $admin = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
        'status' => 'closed',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);

    $response = $this
        ->actingAs($admin)
        ->patch(route('groups.status.update', $group), ['status' => 'active']);

    $response->assertSessionHasErrors('status');
    expect($group->fresh()->status)->toBe('closed');
});

test('status change sends notification to all members', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
        'status' => 'active',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member->id, ['role' => 'member']);

    $this
        ->actingAs($admin)
        ->patch(route('groups.status.update', $group), ['status' => 'settled']);

    $adminNotification = $admin->appNotifications()->where('type', 'group.status_changed')->first();
    $memberNotification = $member->appNotifications()->where('type', 'group.status_changed')->first();

    expect($adminNotification)->not->toBeNull();
    expect($memberNotification)->not->toBeNull();
    expect($memberNotification->message)->toContain('settled');
});

test('invalid status is rejected', function () {
    $admin = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
        'status' => 'active',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);

    $response = $this
        ->actingAs($admin)
        ->patch(route('groups.status.update', $group), ['status' => 'invalid']);

    $response->assertSessionHasErrors('status');
    expect($group->fresh()->status)->toBe('active');
});
