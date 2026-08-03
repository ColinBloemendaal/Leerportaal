<?php

declare(strict_types=1);

namespace App\Actions\Mail;

use App\DataTransferObjects\Mail\MailSuppressionEventData;
use App\Models\SuppressedEmail;

/**
 * Idempotent by design: the same webhook delivery arriving twice (a
 * provider's own at-least-once retry policy) just refreshes the same row
 * rather than creating a duplicate, keyed on the unique `email` column.
 */
final readonly class RecordMailSuppression
{
    public function __invoke(MailSuppressionEventData $event): SuppressedEmail
    {
        return SuppressedEmail::query()->updateOrCreate(
            ['email' => $event->email],
            [
                'reason' => $event->reason,
                'provider_event_type' => $event->providerEventType,
                'occurred_at' => $event->occurredAt,
            ],
        );
    }
}
