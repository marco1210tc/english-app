<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'total_points',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // --- RELACIONES ---

    // Relaciones como Maestro (Teacher)
    public function taughtClassrooms()
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'teacher_id');
    }

    // Relaciones como Estudiante (Student)
    public function classrooms()
    {
        // Un estudiante pertenece a muchas aulas
        return $this->belongsToMany(Classroom::class, 'classroom_user');
    }

    public function taskSubmissions()
    {
        return $this->hasMany(TaskSubmission::class, 'user_id');
    }

    public function challengeSubmissions()
    {
        return $this->hasMany(ChallengeSubmission::class, 'user_id');
    }

    public function achievements()
    {
        // Un estudiante puede tener muchos logros
        // Le decimos a Laravel que la tabla solo tiene 'unlocked_at'
        return $this->belongsToMany(Achievement::class, 'achievement_user')
            ->withPivot('unlocked_at') // Le decimos que columna custom existe
            ->using(AchievementUser::class) // Le decimos que use el modelo pivot
            ->as('pivot'); // Nombre del objeto pivot
    }
}
