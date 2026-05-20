<?php

use App\Models\User;
use App\Models\Client;
use App\Models\Trainer;
use App\Models\MembershipPlan;
use App\Models\ClientMembership;
use App\Models\Payment;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

// 1. User Validations
test('user store validation fails with invalid data', function () {
    $response = $this->post(route('admin.users.store'), [
        'name' => 'Ab', // too short
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
        'role' => 'invalid-role',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});

test('user update validation fails with invalid data', function () {
    $user = User::factory()->create(['role' => 'staff']);

    $response = $this->put(route('admin.users.update', $user), [
        'name' => '', // required
        'email' => 'invalid-email',
        'role' => 'invalid-role',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'role']);
});

// 2. Client Validations
test('client store validation fails with invalid data', function () {
    $response = $this->post(route('admin.clients.store'), [
        'name' => 'A', // too short
        'email' => 'invalid',
        'phone' => '123', // too short (min 10)
        'membership_status' => 'invalid-status',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'phone', 'membership_status']);
});

test('client update validation fails with invalid data', function () {
    $client = Client::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '1234567890',
        'membership_status' => 'sin_membresia',
    ]);

    $response = $this->put(route('admin.clients.update', $client), [
        'name' => '',
        'email' => 'invalid-email',
        'phone' => '1234567890123456', // too long (max 15)
        'membership_status' => 'sin_membresia',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'phone']);
});

// 3. Trainer Validations
test('trainer store validation fails with invalid data', function () {
    $response = $this->post(route('admin.trainers.store'), [
        'name' => 'A',
        'email' => 'invalid-email',
        'phone' => '123',
        'specialty' => '', // required
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'phone', 'specialty']);
});

// 4. GymClass Validations
test('class store validation fails with invalid data', function () {
    $response = $this->post(route('admin.classes.store'), [
        'name' => 'Ab',
        'trainer_id' => 9999, // non-existing
        'max_capacity' => 0, // must be min 1
        'schedule' => json_encode([
            [
                'day' => 'Lunes',
                'start' => '10:00',
                'end' => '09:00', // end must be after start
            ]
        ]),
    ]);

    $response->assertSessionHasErrors(['name', 'trainer_id', 'max_capacity', 'schedule.0.end']);
});

// 5. MembershipPlan Validations
test('membership plan store validation fails with invalid data', function () {
    $response = $this->post(route('admin.membership-plans.store'), [
        'name' => 'Ab',
        'duration_days' => 0, // must be min 1
        'price' => -10, // must be min 0
    ]);

    $response->assertSessionHasErrors(['name', 'duration_days', 'price']);
});

// 6. ClientMembership Validations
test('client membership store validation fails with invalid data', function () {
    $client = Client::create([
        'name' => 'John Doe',
        'email' => 'john2@example.com',
        'phone' => '1234567890',
        'membership_status' => 'sin_membresia',
    ]);

    $response = $this->post(route('admin.client-memberships.store', $client), [
        'membership_plan_id' => 9999, // non-existing
        'start_date' => '2026-05-20',
        'end_date' => '2026-05-19', // end must be after start
    ]);

    $response->assertSessionHasErrors(['membership_plan_id', 'end_date']);
});

// 7. Payment Validations
test('payment store validation fails with invalid data', function () {
    $response = $this->post(route('admin.payments.store'), [
        'client_id' => 9999,
        'membership_plan_id' => 9999,
        'amount' => 0, // must be min 1
        'payment_method' => 'invalid-method',
    ]);

    $response->assertSessionHasErrors(['client_id', 'membership_plan_id', 'amount', 'payment_method']);
});
