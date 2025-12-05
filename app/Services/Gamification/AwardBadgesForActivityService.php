<?php

namespace App\Services\Gamification;

use App\Models\StudentActivityAttempt;

class AwardBadgesForActivityService
{
    public function __construct(
        protected BadgeService $badgeService,
    ) {}

    public function handle(StudentActivityAttempt $attempt): void
    {
        $this->badgeService->evaluateBadgesForActivityAttempt($attempt);
    }
}
