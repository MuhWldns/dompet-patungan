<?php

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;

test('admin can kick a member from group', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member->id, ['role' => 'member']);

    $response = $this
        ->actingAs($admin)
        ->delete(route('groups.members.destroy', [$group, $member]));

    $response->assertRedirect();
    expect($group->members()->whereKey($member->id)->exists())->toBeFalse();
});

test('non-admin cannot kick another member', function () {
    $admin = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member1->id, ['role' => 'member']);
    $group->members()->attach($member2->id, ['role' => 'member']);

    $response = $this
        ->actingAs($member1)
        ->delete(route('groups.members.destroy', [$group, $member2]));

    $response->assertForbidden();
    expect($group->members()->whereKey($member2->id)->exists())->toBeTrue();
});

test('member can leave a group', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member->id, ['role' => 'member']);

    $response = $this
        ->actingAs($member)
        ->delete(route('groups.members.destroy', [$group, $member]));

    $response->assertRedirect();
    expect($group->members()->whereKey($member->id)->exists())->toBeFalse();
});

test('non-member cannot access destroy route', function () {
    $admin = User::factory()->create();
    $outsider = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);

    $response = $this
        ->actingAs($outsider)
        ->delete(route('groups.members.destroy', [$group, $admin]));

    $response->assertForbidden();
});

test('last admin leaving promotes oldest member to admin', function () {
    $admin = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member1->id, ['role' => 'member']);
    $group->members()->attach($member2->id, ['role' => 'member']);

    $this
        ->actingAs($admin)
        ->delete(route('groups.members.destroy', [$group, $admin]));

    $newAdmin = $group->members()->wherePivot('role', 'admin')->first();
    expect($newAdmin->id)->toBe($member1->id);
});

test('last member leaving closes the group', function () {
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $member->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
        'status' => 'active',
    ]);
    $group->members()->attach($member->id, ['role' => 'admin']);

    $this
        ->actingAs($member)
        ->delete(route('groups.members.destroy', [$group, $member]));

    expect($group->fresh()->status)->toBe('closed');
});

test('kicked member receives notification', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member->id, ['role' => 'member']);

    $this
        ->actingAs($admin)
        ->delete(route('groups.members.destroy', [$group, $member]));

    $notification = $member->appNotifications()->where('type', 'member.removed')->first();
    expect($notification)->not->toBeNull();
    expect($notification->message)->toContain('Test Group');
});

test('leaving member triggers notification to remaining members', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $admin->id,
        'name' => 'Test Group',
        'invite_token' => 'test-token',
    ]);
    $group->members()->attach($admin->id, ['role' => 'admin']);
    $group->members()->attach($member->id, ['role' => 'member']);

    $this
        ->actingAs($member)
        ->delete(route('groups.members.destroy', [$group, $member]));

    $notification = $admin->appNotifications()->where('type', 'member.left')->first();
    expect($notification)->not->toBeNull();
    expect($notification->message)->toContain($member->name);
});
