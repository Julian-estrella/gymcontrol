<?php

use App\Models\User;
use App\Models\Client;
use App\Models\MembershipPlan;
use App\Models\ClientMembership;
use App\Models\Payment;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

test('registering a paid payment assigns membership to client', function () {
    // 1. Arrange
    $client = Client::create([
        'name' => 'Test Client',
        'email' => 'client@test.com',
        'phone' => '1234567890',
        'membership_status' => 'sin_membresia',
    ]);

    $plan = MembershipPlan::create([
        'name' => 'Monthly Plan',
        'duration_days' => 30,
        'price' => 500.00,
        'is_active' => true,
    ]);

    // 2. Act
    $response = $this->post(route('admin.payments.store'), [
        'client_id' => $client->id,
        'membership_plan_id' => $plan->id,
        'amount' => 500.00,
        'payment_method' => 'cash',
        'status' => 'paid',
        'notes' => 'Pago de membresia mensual',
    ]);

    // 3. Assert
    $response->assertRedirect(route('admin.payments.index'));
    
    // Check client status is now 'activo'
    $client->refresh();
    expect($client->membership_status)->toBe('activo');

    // Check ClientMembership was created and is active
    $membership = ClientMembership::where('client_id', $client->id)->first();
    expect($membership)->not->toBeNull();
    expect($membership->membership_plan_id)->toBe($plan->id);
    expect($membership->status)->toBe('activo');
    expect($membership->start_date->toDateString())->toBe(now()->toDateString());
    expect($membership->end_date->toDateString())->toBe(now()->addDays(30)->toDateString());

    // Check payment is associated with the ClientMembership
    $payment = Payment::where('client_id', $client->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->status)->toBe('paid');
    expect($payment->client_membership_id)->toBe($membership->id);
});

test('admin can deactivate a client membership', function () {
    // 1. Arrange
    $client = Client::create([
        'name' => 'Test Client',
        'email' => 'client@test.com',
        'phone' => '1234567890',
        'membership_status' => 'activo',
    ]);

    $plan = MembershipPlan::create([
        'name' => 'Monthly Plan',
        'duration_days' => 30,
        'price' => 500.00,
        'is_active' => true,
    ]);

    $membership = ClientMembership::create([
        'client_id' => $client->id,
        'membership_plan_id' => $plan->id,
        'start_date' => now(),
        'end_date' => now()->addDays(30),
        'status' => 'activo',
    ]);

    // 2. Act
    $response = $this->patch(route('admin.client-memberships.deactivate', $membership->id));

    // 3. Assert
    $response->assertRedirect(route('admin.clients.show', $client->id));

    // Check membership is vencido
    $membership->refresh();
    expect($membership->status)->toBe('vencido');

    // Check client status is updated to sin_membresia (since no other active memberships exist)
    $client->refresh();
    expect($client->membership_status)->toBe('sin_membresia');

    // Check history was logged
    $this->assertDatabaseHas('membership_histories', [
        'client_id' => $client->id,
        'status' => 'sin_membresia',
        'observations' => 'Membresía desactivada manualmente por admin.',
    ]);
});

test('admin can cancel a client membership', function () {
    // 1. Arrange
    $client = Client::create([
        'name' => 'Test Client',
        'email' => 'client@test.com',
        'phone' => '1234567890',
        'membership_status' => 'activo',
    ]);

    $plan = MembershipPlan::create([
        'name' => 'Monthly Plan',
        'duration_days' => 30,
        'price' => 500.00,
        'is_active' => true,
    ]);

    $membership = ClientMembership::create([
        'client_id' => $client->id,
        'membership_plan_id' => $plan->id,
        'start_date' => now(),
        'end_date' => now()->addDays(30),
        'status' => 'activo',
    ]);

    // 2. Act
    $response = $this->patch(route('admin.client-memberships.cancel', $membership->id));

    // 3. Assert
    $response->assertRedirect(route('admin.clients.show', $client->id));

    // Check membership is cancelado
    $membership->refresh();
    expect($membership->status)->toBe('cancelado');

    // Check client status is updated to sin_membresia
    $client->refresh();
    expect($client->membership_status)->toBe('sin_membresia');

    // Check history was logged
    $this->assertDatabaseHas('membership_histories', [
        'client_id' => $client->id,
        'status' => 'sin_membresia',
        'observations' => 'Membresía cancelada manualmente por admin.',
    ]);
});
