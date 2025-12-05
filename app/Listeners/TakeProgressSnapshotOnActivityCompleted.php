<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Events\StudentActivityCompleted;
use App\Services\Progress\UpdateStudentProgressService;

class TakeProgressSnapshotOnActivityCompleted
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected UpdateStudentProgressService $updateStudentProgress
    ) {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        //
    }
}
