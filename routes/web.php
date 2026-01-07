<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ActivityController;
use App\Http\Controllers\Student\SessionController;
use App\Http\Controllers\Student\LessonsController;
use App\Http\Controllers\Teacher\ClassroomLessonAssignmentController;
use App\Livewire\Teacher\Classrooms\Index as TeacherClassroomsIndex;
use App\Http\Controllers\StudentAuthController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Teacher\Classrooms\LessonsManager;
use App\Models\Lesson;
use App\Livewire\Student\Session\Player;
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
        Route::get('/dashboard', fn() => view('student.dashboard'))
            ->name('student.dashboard');
        Route::get('/lessons', [LessonsController::class, 'index'])
            ->name('student.lessons.index');
        // DETALLE (para botón empezar)
        Route::get('/lessons/{assignmentId}', [LessonsController::class, 'show'])
            ->name('student.lessons.show');
        // PLAYER
        Route::get('/session/{assignmentId}', Player::class)
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
        Route::get('/classrooms/{classroom}/lessons', LessonsManager::class)
            ->name('teacher.classrooms.lessons');

        // acciones
        Route::post('/classrooms/{classroom}/lessons/assign', [ClassroomLessonAssignmentController::class, 'store'])
            ->name('teacher.classrooms.lessons.assign');

        Route::patch('/classrooms/{classroom}/lessons/{lessonId}', [ClassroomLessonAssignmentController::class, 'update'])
            ->name('teacher.classrooms.lessons.update');

        Route::delete('/classrooms/{classroom}/lessons/{lessonId}', [ClassroomLessonAssignmentController::class, 'destroy'])
            ->name('teacher.classrooms.lessons.destroy');
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
