<?php

declare(strict_types=1);

namespace App\Enums;

enum CourseCompletionRuleType: string
{
    case AllLessons = 'all_lessons';
    case PassExam = 'pass_exam';
    case MinimumScore = 'minimum_score';

    public function label(): string
    {
        return match ($this) {
            self::AllLessons => 'Complete every lesson',
            self::PassExam => 'Pass a specific exam',
            self::MinimumScore => 'Reach a minimum exam score',
        };
    }
}
