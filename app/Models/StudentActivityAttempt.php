<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentActivityAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'activity_id',
        'started_at',
        'finished_at',
        'duration_seconds',
        'score',
        'max_score',
        'correct_count',
        'wrong_count',
        'raw_payload',
        'meta_json',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'raw_payload' => 'array',
        'meta_json'   => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function questionAttempts()
    {
        return $this->hasMany(StudentQuestionAttempt::class);
    }
}
