<?php

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
