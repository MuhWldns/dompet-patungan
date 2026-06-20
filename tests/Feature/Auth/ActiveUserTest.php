<?php

use App\Models\User;

test('inactive users are redirected away from authenticated app pages', function () {
    $user = User::factory()->create(['is_active' => false]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});
