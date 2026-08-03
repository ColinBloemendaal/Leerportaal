<?php

declare(strict_types=1);

use App\Enums\NotificationType;

it('has a non-empty label for every case', function (): void {
    foreach (NotificationType::cases() as $case) {
        expect($case->label())->not->toBe('');
    }
});

it('matches the catalogue named in TODO.md', function (): void {
    $values = array_map(fn (NotificationType $case): string => $case->value, NotificationType::cases());

    expect($values)->toBe([
        'welcome',
        'invite',
        'assignment',
        'deadline',
        'overdue',
        'completion',
        'certificate',
        'password_reset',
        'billing',
        'admin_alert',
    ]);
});
