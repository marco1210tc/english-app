<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgressSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'classroom_id', // nullable
        'module_id',    // nullable
        'lesson_id',    // nullable
        'taken_at',
        'data_json',    // { "completed_activities": X, "avg_score": Y, ... }
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'data_json' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
