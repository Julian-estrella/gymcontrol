<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\TrainerController;
use App\Http\Controllers\Admin\GymClassController;
use App\Http\Controllers\Admin\MembershipPlanController;
use App\Http\Controllers\Admin\ClientMembershipController;
use App\Http\Controllers\Admin\RoleController;


/*
|--------------------------------------------------------------------------
| Web Routes — GymControl
|--------------------------------------------------------------------------
*/

// Root: show welcome page
Route::get('/', function () {
    return view('welcome');
});

// ─── Protected Routes ────────────────────────────────────────────────────────
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard Router: redirects to correct dashboard based on role
    Route::get('dashboard', function () {
        return redirect()->route(auth()->user()->dashboardRoute());
    })->name('dashboard');

    // 1. Admin Dashboard + shared resources
    Route::middleware(['admin.access'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Clients Management
        Route::middleware('admin.module:clients')->resource('clients', ClientController::class);

        // Trainers Management
        Route::middleware('admin.module:trainers')->group(function () {
            Route::resource('trainers', TrainerController::class);
            Route::patch('trainers/{trainer}/toggle', [TrainerController::class, 'toggle'])->name('trainers.toggle');
        });

        // GymClasses Management
        Route::middleware('admin.module:classes')->group(function () {
            Route::resource('classes', GymClassController::class);
            Route::post('classes/{class}/enroll', [GymClassController::class, 'enroll'])->name('classes.enroll');
            Route::delete('classes/{class}/unenroll/{client}', [GymClassController::class, 'unenroll'])->name('classes.unenroll');
        });

        // Membership Plans
        Route::middleware('admin.module:membership-plans')->group(function () {
            Route::resource('membership-plans', MembershipPlanController::class);
            Route::patch('membership-plans/{membershipPlan}/toggle', [MembershipPlanController::class, 'toggle'])->name('membership-plans.toggle');
        });

        // Client Memberships (assign/cancel/status)
        Route::middleware('admin.module:clients')->group(function () {
            Route::get('clients/{client}/memberships/create', [ClientMembershipController::class, 'create'])->name('client-memberships.create');
            Route::post('clients/{client}/memberships', [ClientMembershipController::class, 'store'])->name('client-memberships.store');
            Route::patch('client-memberships/{clientMembership}/cancel', [ClientMembershipController::class, 'destroy'])->name('client-memberships.cancel');
            Route::patch('client-memberships/{clientMembership}/deactivate', [ClientMembershipController::class, 'deactivate'])->name('client-memberships.deactivate');
            Route::patch('client-memberships/{clientMembership}/status', [ClientMembershipController::class, 'updateStatus'])->name('client-memberships.status');
        });

        // Payments
        Route::middleware('admin.module:payments')->group(function () {
            Route::resource('payments', App\Http\Controllers\Admin\PaymentController::class)->except(['edit', 'update']);
            Route::patch('payments/{payment}/cancel', [App\Http\Controllers\Admin\PaymentController::class, 'cancel'])->name('payments.cancel');
            Route::get('payments/{payment}/pdf', [App\Http\Controllers\Admin\PaymentController::class, 'downloadPdf'])->name('payments.pdf');
        });

        // Security
        Route::middleware('admin.module:users')->resource('users', UserController::class)->except('show');
        Route::middleware('admin.module:roles')->resource('roles', RoleController::class)->except('show');
    });

    // 3. Cliente Dashboard
    Route::middleware(['role:cliente,staff,admin'])->group(function () {
        Route::view('mi-gym', 'dashboard')->name('cliente.dashboard');
    });

    // Profile
    Route::get('profile', function () {
        return redirect()->route('profile.show');
    })->name('profile');
});
