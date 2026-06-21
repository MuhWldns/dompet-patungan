<?php

use App\Models\Group;
use App\Models\User;

test('authenticated users can create groups and become group admins', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('groups.store'), [
            'name' => 'Trip Bali',
            'description' => 'Patungan liburan',
            'target_amount' => '1500000',
        ]);

    $group = Group::query()->where('name', 'Trip Bali')->firstOrFail();

    $response->assertRedirect(route('groups.show', $group));
    expect($group->members()->whereKey($user->id)->wherePivot('role', 'admin')->exists())->toBeTrue();
});

test('users can join groups with invite tokens as members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Kos Bersama',
        'invite_token' => 'invite-token',
    ]);

    $response = $this
        ->actingAs($member)
        ->post(route('groups.join', [$group, 'invite-token']));

    $response->assertRedirect(route('groups.show', $group));
    expect($group->members()->whereKey($member->id)->wherePivot('role', 'member')->exists())->toBeTrue();
});

test('non members cannot view group details', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Private Group',
        'invite_token' => 'private-token',
    ]);

    $response = $this
        ->actingAs($outsider)
        ->get(route('groups.show', $group));

    $response->assertForbidden();
});

test('members can view group details', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Visible Group',
        'invite_token' => 'visible-token',
    ]);
    $group->members()->attach($member->id, ['role' => 'member']);

    $response = $this
        ->actingAs($member)
        ->get(route('groups.show', $group));

    $response->assertOk();
});

test('authenticated users can view group invite confirmation pages with valid tokens', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Weekend Trip',
        'description' => 'Shared travel costs',
        'invite_token' => 'weekend-token',
    ]);

    $response = $this
        ->actingAs($member)
        ->get(route('groups.join.show', [$group, 'weekend-token']));

    $response
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('groups/Join')
            ->where('group.id', $group->id)
            ->where('group.name', 'Weekend Trip')
            ->where('group.description', 'Shared travel costs')
            ->where('group.status', 'active')
            ->where('group.members_count', 0)
            ->where('isMember', false)
        );
});

test('guests opening group invite confirmation pages are redirected to login', function () {
    $owner = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Guest Invite',
        'invite_token' => 'guest-token',
    ]);

    $response = $this->get(route('groups.join.show', [$group, 'guest-token']));

    $response->assertRedirect(route('login'));
});

test('group invite confirmation pages return not found for invalid tokens', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Invalid Invite',
        'invite_token' => 'valid-token',
    ]);

    $response = $this
        ->actingAs($member)
        ->get(route('groups.join.show', [$group, 'wrong-token']));

    $response->assertNotFound();
});

test('existing members can view group invite confirmation pages without duplicate membership', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::query()->create([
        'creator_id' => $owner->id,
        'name' => 'Existing Member Invite',
        'invite_token' => 'existing-token',
    ]);
    $group->members()->attach($member->id, ['role' => 'member']);

    $response = $this
        ->actingAs($member)
        ->get(route('groups.join.show', [$group, 'existing-token']));

    $response
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('groups/Join')
            ->where('isMember', true)
            ->where('group.members_count', 1)
        );

    expect($group->members()->whereKey($member->id)->count())->toBe(1);
});
