<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Notifications;

use App\Enums\DigestFrequency;

final readonly class UpdateNotificationDigestFrequencyData
{
    public function __construct(public DigestFrequency $frequency) {}
}
