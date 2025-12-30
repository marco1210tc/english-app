<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;


class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'pin_hash',
        'classroom_id',
        'avatar',
        'status',
        'first_name',
        'last_name',
    ];

    protected $hidden = ['pin_hash'];

    public function verifyPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }
    
    //Relaciones
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class)
            ->withTimestamps();
    }

    //helpers
    public function activityAttempts()
    {
        return $this->hasMany(StudentActivityAttempt::class);
    }

    public function questionAttempts()
    {
        return $this->hasManyThrough(
            StudentQuestionAttempt::class,
            StudentActivityAttempt::class,
            'student_id',                  // Foreign key en StudentActivityAttempt
            'student_activity_attempt_id', // Foreign key en StudentQuestionAttempt
            'id',
            'id'
        );
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'student_badges')
            ->withPivot('awarded_at', 'meta_json')
            ->withTimestamps();
    }

    public function progressSnapshots()
    {
        return $this->hasMany(ProgressSnapshot::class);
    }
}
