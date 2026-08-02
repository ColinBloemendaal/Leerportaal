<?php

declare(strict_types=1);

namespace App\Enums;

enum FeedbackVisibility: string
{
    case Immediate = 'immediate';
    case AfterSubmission = 'after_submission';
    case Never = 'never';

    public function label(): string
    {
        return match ($this) {
            self::Immediate => 'Immediately after answering',
            self::AfterSubmission => 'After the quiz is submitted',
            self::Never => 'Never shown',
        };
    }
}
