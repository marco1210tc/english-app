<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentQuestionAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_activity_attempt_id',
        'question_id',  // nullable
        'item_key',     // nullable, para drag_drop & otros
        'is_correct',
        'score',
        'raw_answer_json',
    ];

    protected $casts = [
        'is_correct'      => 'boolean',
        'raw_answer_json' => 'array',
    ];

    public function activityAttempt()
    {
        return $this->belongsTo(StudentActivityAttempt::class, 'student_activity_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
