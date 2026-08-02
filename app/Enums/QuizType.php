<?php

declare(strict_types=1);

namespace App\Enums;

enum QuizType: string
{
    case Practice = 'practice';
    case Exam = 'exam';

    public function label(): string
    {
        return match ($this) {
            self::Practice => 'Practice',
            self::Exam => 'Exam',
        };
    }
}
