<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ActivityController;
use App\Http\Controllers\Student\SessionController;
use App\Http\Controllers\Student\LessonsController;
use App\Livewire\Teacher\Classrooms\Index as TeacherClassroomsIndex;
use App\Http\Controllers\StudentAuthController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Teacher\Classrooms\LessonsManager;
use App\Models\Lesson;
use App\Models\Classroom;
use App\Livewire\Student\Session\Player;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;


Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::prefix('s')->group(function () {
    // Login estudiante    
    Route::post('/login', [StudentAuthController::class, 'login'])
        ->name('student.login.submit');    
    // Logout estudiante
    Route::post('/logout', [StudentAuthController::class, 'logout'])
        ->name('student.logout');
    // Rutas protegidas estudiante
    Route::middleware('auth:student')->group(function () {
        Route::get('/dashboard', fn() => view('student.dashboard'))
            ->name('student.dashboard');
        Route::get('/lessons', [LessonsController::class, 'index'])
            ->name('student.lessons.index');
        // DETALLE (para botón empezar)
        Route::get('/lessons/{assignmentId}', [LessonsController::class, 'show'])
            ->name('student.lessons.show');
        // PLAYER
        Route::get('/session/{assignmentId}', fn($assignmentId) => 
            view('student.session.play', compact('assignmentId')))
            ->name('student.session.play');  
    });
});

Route::middleware(['auth', 'role:teacher,admin'])
    ->prefix('teacher')
    ->group(function () {

        Route::get('dashboard', fn() => view('teacher.dashboard'))->name('teacher.dashboard');

        Route::get('/classrooms', TeacherClassroomsIndex::class)
            ->name('teacher.classrooms.index');

        // UI livewire manager
        Route::get('/classrooms/{classroom}/lessons', fn(Classroom $classroom) => 
            view('teacher.classrooms.lessons-manager', compact('classroom')))
            ->name('teacher.classrooms.lessons');
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

