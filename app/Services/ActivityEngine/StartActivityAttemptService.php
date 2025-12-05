<?php

namespace App\Services\ActivityEngine;

use App\Models\Activity;
use App\Models\Student;
use App\Models\StudentActivityAttempt;

class StartActivityAttemptService
{
    public function start(Activity $activity, Student $student): StudentActivityAttempt
    {
        return StudentActivityAttempt::create([
            'student_id'  => $student->id,
            'activity_id' => $activity->id,
            'started_at'  => now(),
        ]);
    }
}
