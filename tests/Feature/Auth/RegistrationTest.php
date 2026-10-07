<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    expect(auth()->user()->role)->toBe(User::ROLE_STUDENT);
});

test('public registration cannot assign a privileged role', function () {
    $this->post('/register', [
        'name' => 'Untrusted Administrator',
        'email' => 'untrusted@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => User::ROLE_ADMINISTRATOR,
    ])->assertRedirect(route('dashboard', absolute: false));

    expect(User::where('email', 'untrusted@example.com')->value('role'))
        ->toBe(User::ROLE_STUDENT);
});
