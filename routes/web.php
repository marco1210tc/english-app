<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ActivityController;
use App\Http\Controllers\StudentAuthController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;


Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Route::view('entrar', 'entrar');

Route::prefix('s')->group(function () {

    // Login estudiante
    Route::post('/login', [StudentAuthController::class, 'login'])
        ->name('student.login.submit');

    // Logout estudiante
    Route::post('/logout', [StudentAuthController::class, 'logout'])
        ->name('student.logout');

    // Rutas protegidas estudiante
    Route::middleware('auth:student')->group(function () {
        Route::get('/dashboard', fn () => view('student.dashboard'))
            ->name('student.dashboard');
    });
});

Route::middleware(['auth', 'role:teacher,admin'])
    ->prefix('teacher')
    ->group(function () {
        // rutas teacher
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        // rutas admin
    });

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

