<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Mail;

use App\Enums\SuppressionReason;
use Carbon\CarbonImmutable;

final readonly class MailSuppressionEventData
{
    public function __construct(
        public string $email,
        public SuppressionReason $reason,
        public CarbonImmutable $occurredAt,
        public string $providerEventType,
    ) {}
}
