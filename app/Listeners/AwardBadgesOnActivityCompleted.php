<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Events\StudentActivityCompleted;
use App\Services\Gamification\AwardBadgesForActivityService;

class AwardBadgesOnActivityCompleted
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected AwardBadgesForActivityService $awardBadges
    ) {}

    /**
     * Handle the event.
     */
    public function handle(StudentActivityCompleted $event): void
    {
        $this->awardBadges->handle($event->attempt);
    }
}
