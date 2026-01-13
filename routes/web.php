<?php

use App\Http\Controllers\Teacher\ClassroomResultsExportController;
use App\Http\Controllers\Teacher\AttemptExportController;
use App\Http\Controllers\Teacher\StudentExportController;
use App\Http\Controllers\Student\LessonsController;
use App\Livewire\Teacher\Classrooms\Index as TeacherClassroomsIndex;
use App\Http\Controllers\StudentAuthController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Models\Classroom;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Livewire\Teacher\Classrooms\StudentResults;
use App\Livewire\Teacher\Classrooms\AttemptDetail;
// use App\Livewire\Teacher\Classrooms\AttemptGameDetail;


// Route::get('/', function () {
//     return view('welcome');
// })->name('lohin');

Route::get('/', fn() => redirect(route('login')));

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
            ->whereNumber('assignmentId')
            ->name('student.session.play');
    });
});

Route::middleware(['auth', 'role:teacher,admin'])
    ->prefix('teacher')
    ->group(function () {

        Route::get('dashboard', fn() => view('teacher.dashboard'))->name('teacher.dashboard');

        Route::get('/classrooms', TeacherClassroomsIndex::class)
            ->name('teacher.classrooms.index');

        // LECCIONES
        Route::get('/classrooms/{classroom}/lessons', fn(Classroom $classroom) =>
        view('teacher.classrooms.lessons-manager', compact('classroom')))
            ->name('teacher.classrooms.lessons');

        Route::get('/classrooms/{classroom}/results', fn(Classroom $classroom) =>
        view('teacher.classrooms.results', compact('classroom')))
            ->name('teacher.classrooms.results');

        Route::get('/classrooms/{classroom}/results/export', [ClassroomResultsExportController::class, 'export'])
            ->name('teacher.classrooms.results.export');

        Route::get('/classrooms/{classroom}/results/{student}', StudentResults::class)
            ->name('teacher.classrooms.results.student');

        Route::get('/classrooms/{classroom}/attempts/{attempt}', AttemptDetail::class)
            ->name('teacher.classrooms.attempts.show');

        Route::get('/classrooms/{classroom}/attempts/{attempt}/export', [AttemptExportController::class, 'export'])
            ->name('teacher.classrooms.attempts.export');

        Route::get('/classrooms/{classroom}/students/{student}/export', [StudentExportController::class, 'export'])
            ->name('teacher.classrooms.students.export');

        // Route::get('/classrooms/{classroom}/results/{student}/attempts/{attempt}/games', AttemptGameDetail::class)
        //     ->name('teacher.classrooms.results.attempt.games');
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
