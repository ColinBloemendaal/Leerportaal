<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Notifications;

final readonly class NotificationPreferenceRowData
{
    /**
     * @param  array<string, bool>  $channels  channel value => enabled
     */
    public function __construct(
        public string $type,
        public string $label,
        public array $channels,
    ) {}
}
