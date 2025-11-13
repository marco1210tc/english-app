<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Models\Classroom;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ClassroomController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('student', function () {
    return view('student.index');
})->name('student');

Route::get('student/ranking', function () {
    return view('student.ranking');
})->name('ranking');

Route::get('teacher', function () {
    return view('teacher.index');
})->name('teacher');

Route::get('teacher/groups', [ClassroomController::class, 'index'])->name('groups.index'); //solo para testear
Route::get('teacher/groups/{group}', [ClassroomController::class, 'show'])->name('groups.show'); //solo para testear
Route::get('teacher/groups/create', [ClassroomController::class, 'create'])->name('groups.create'); //solo para testear
Route::post('teacher/groups', [ClassroomController::class, 'store'])->name('groups.store'); //solo para testear

Route::get('users', [UsersController::class, 'index'])->name('users'); //solo para testear

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

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

require __DIR__ . '/auth.php';
