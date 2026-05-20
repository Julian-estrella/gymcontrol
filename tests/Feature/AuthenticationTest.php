<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('admin redirects to admin dashboard', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');

    $finalResponse = $this->get('/dashboard');
    $finalResponse->assertRedirect(route('admin.dashboard'));
});

test('staff redirects to admin dashboard', function () {
    $user = User::factory()->create(['role' => 'staff']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');

    $finalResponse = $this->get('/dashboard');
    $finalResponse->assertRedirect(route('admin.dashboard'));
});

test('cliente redirects to cliente dashboard', function () {
    $user = User::factory()->create(['role' => 'cliente']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');

    $finalResponse = $this->get('/dashboard');
    $finalResponse->assertRedirect(route('cliente.dashboard'));
});


