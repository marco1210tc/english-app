<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\Database;
use Illuminate\Support\Facades\Hash;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureAuthentication();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(function () {
            $data = new Database();
            $students = $data->students();
            return view('login', ['students' => $students]);
        });
        Fortify::verifyEmailView(fn() => view('livewire.auth.verify-email'));
        Fortify::twoFactorChallengeView(fn() => view('livewire.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn() => view('livewire.auth.confirm-password'));
        Fortify::registerView(fn() => view('livewire.auth.register'));
        Fortify::resetPasswordView(fn() => view('livewire.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn() => view('livewire.auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }

    /**
     * Configure authentication behavior for login (teachers vs students).
     */
    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            // 1) LOGIN DE ESTUDIANTE: student_code  pin
            if ($request->filled('student_code') && $request->filled('pin')) {
                // Buscar al estudiante por su "code"
                $student = Student::where('code', $request->input('student_code'))
                    ->whereHas('user', fn($q) => $q->where('role', 'student'))
                    ->first();

                if ($student && $student->pin_hash && Hash::check($request->input('pin'), $student->pin_hash)) {
                    // MUY IMPORTANTE: devolver el User, no el Student
                    return $student->user;
                }

                // Si falla, devolvemos null y Fortify devolverá credenciales inválidas
                return null;
            }

            // 2) LOGIN DOCENTE / ADMIN: email  password
            if ($request->filled('email') && $request->filled('password')) {
                $user = User::where('email', $request->input('email'))->first();

                if ($user && Hash::check($request->input('password'), $user->password)) {
                    return $user;
                }
            }

            return null;
        });

        // (Recomendable) redirección post-login según rol
        Fortify::redirects('login', function () {
            $user = \Illuminate\Support\Facades\Auth::auth()->user();

            if (! $user) {
                return '/login';
            }

            return match ($user->role) {
                'teacher' => route('teacher.dashboard'),
                'admin'   => route('dashboard'),
                default   => '/dashboard',
            };
        });
    }
}
