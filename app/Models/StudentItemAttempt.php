<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentItemAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_attempt_id',
        'item_key',
        'is_correct',
        'attempts',
        'time_spent_seconds',
        'hints_used',
        'response_json',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'attempts' => 'integer',
        'time_spent_seconds' => 'integer',
        'hints_used' => 'integer',
        'response_json' => 'array',
    ];

    public function activityAttempt()
    {
        return $this->belongsTo(StudentActivityAttempt::class, 'activity_attempt_id');
    }
}
