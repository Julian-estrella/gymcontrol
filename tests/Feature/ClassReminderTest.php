<?php

use App\Models\User;
use App\Models\Client;
use App\Models\Trainer;
use App\Models\GymClass;
use App\Mail\ClassReminderMail;
use Illuminate\Support\Facades\Mail;

test('sends email reminders to clients enrolled in tomorrow classes', function () {
    Mail::fake();

    // 1. Arrange: Create trainer and client
    $trainer = Trainer::create([
        'name' => 'John Trainer',
        'email' => 'john.t@example.com',
        'phone' => '1234567890',
        'specialty' => 'Yoga',
        'is_active' => true,
    ]);

    $client = Client::create([
        'name' => 'Jane Client',
        'email' => 'jane.c@example.com',
        'phone' => '0987654321',
        'membership_status' => 'activo',
    ]);

    // Determine tomorrow's Spanish day
    $daysTranslation = [
        'Monday'    => 'Lunes',
        'Tuesday'   => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday'  => 'Jueves',
        'Friday'    => 'Viernes',
        'Saturday'  => 'Sábado',
        'Sunday'    => 'Domingo',
    ];
    $tomorrow = now()->addDay();
    $tomorrowSpanish = $daysTranslation[$tomorrow->englishDayOfWeek];
    $tomorrowDateString = $tomorrow->toDateString();

    // Create a GymClass scheduled for tomorrow
    $gymClass = GymClass::create([
        'name' => 'Yoga Class',
        'description' => 'Morning Yoga',
        'trainer_id' => $trainer->id,
        'schedule' => [
            [
                'day' => $tomorrowSpanish,
                'start' => '08:00',
                'end' => '09:00',
            ]
        ],
        'max_capacity' => 10,
        'is_active' => true,
    ]);

    // Enroll client in class
    $gymClass->clients()->attach($client->id);

    // 2. Act: Run the command
    $this->artisan('classes:send-reminders')
        ->expectsOutput("Buscando clases programadas para mañana ({$tomorrowSpanish}, {$tomorrowDateString})...")
        ->expectsOutput("Recordatorio enviado al cliente Jane Client para la clase Yoga Class.")
        ->expectsOutput("Se enviaron 1 recordatorios de clases correctamente.")
        ->assertExitCode(0);

    // 3. Assert: Verify mail was sent and pivot updated
    Mail::assertSent(ClassReminderMail::class, function ($mail) use ($client, $gymClass) {
        return $mail->hasTo($client->email) &&
               $mail->client->id === $client->id &&
               $mail->gymClass->id === $gymClass->id;
    });

    // Check pivot was updated
    $updatedClient = $gymClass->clients()->find($client->id);
    expect($updatedClient->pivot->last_reminder_sent_date)->toBe($tomorrowDateString);
});

test('does not send class reminders if already sent today', function () {
    Mail::fake();

    $trainer = Trainer::create([
        'name' => 'John Trainer',
        'email' => 'john.t@example.com',
        'phone' => '1234567890',
        'specialty' => 'Yoga',
        'is_active' => true,
    ]);

    $client = Client::create([
        'name' => 'Jane Client',
        'email' => 'jane.c@example.com',
        'phone' => '0987654321',
        'membership_status' => 'activo',
    ]);

    $daysTranslation = [
        'Monday'    => 'Lunes',
        'Tuesday'   => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday'  => 'Jueves',
        'Friday'    => 'Viernes',
        'Saturday'  => 'Sábado',
        'Sunday'    => 'Domingo',
    ];
    $tomorrow = now()->addDay();
    $tomorrowSpanish = $daysTranslation[$tomorrow->englishDayOfWeek];
    $tomorrowDateString = $tomorrow->toDateString();

    $gymClass = GymClass::create([
        'name' => 'Yoga Class',
        'description' => 'Morning Yoga',
        'trainer_id' => $trainer->id,
        'schedule' => [
            [
                'day' => $tomorrowSpanish,
                'start' => '08:00',
                'end' => '09:00',
            ]
        ],
        'max_capacity' => 10,
        'is_active' => true,
    ]);

    // Enroll client and set last_reminder_sent_date to tomorrow
    $gymClass->clients()->attach($client->id, [
        'last_reminder_sent_date' => $tomorrowDateString
    ]);

    // Act
    $this->artisan('classes:send-reminders')
        ->expectsOutput("Buscando clases programadas para mañana ({$tomorrowSpanish}, {$tomorrowDateString})...")
        ->expectsOutput("Se enviaron 0 recordatorios de clases correctamente.")
        ->assertExitCode(0);

    // Assert
    Mail::assertNothingSent();
});
