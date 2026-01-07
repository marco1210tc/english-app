<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentActivityAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'activity_id',
        'score_obtained',
        'max_score',
        'started_at',
        'completed_at',
        'status',         // in_progress | completed | abandoned
        'attempt_number',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'activity_id' => 'integer',
        'score_obtained' => 'integer',
        'max_score' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'attempt_number' => 'integer',
    ];


    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function itemAttempts()
    {
        return $this->hasMany(StudentItemAttempt::class, 'activity_attempt_id');
    }
}
