<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertOk();
});

test('registration screen uses dompet patungan product copy', function () {
    $contents = file_get_contents(resource_path('js/pages/auth/Register.vue'));

    expect($contents)->toContain('Daftar Dompet Patungan');
    expect($contents)->not->toContain('Create an account');
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Patungan User',
        'email' => 'patungan@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'patungan@example.com')->firstOrFail();
    expect($user->role)->toBe('user');
    expect($user->is_active)->toBeTrue();
});
