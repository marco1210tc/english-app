<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'grade_id',
        'code',       // código interno del cole, opcional
        'first_name',
        'last_name',
    ];
    
    //Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
