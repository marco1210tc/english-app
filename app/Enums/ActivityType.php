<?php

namespace App\Enums;

enum ActivityType: string
{
    case QUIZ = 'quiz';
    case DRAG_DROP = 'drag_drop';

    public static function values(): array
    {
        return [
            self::QUIZ->value,
            self::DRAG_DROP->value,
        ];
    }

}
