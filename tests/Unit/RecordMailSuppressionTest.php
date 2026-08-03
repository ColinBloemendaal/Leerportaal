<?php

declare(strict_types=1);

use App\Actions\Mail\RecordMailSuppression;
use App\DataTransferObjects\Mail\MailSuppressionEventData;
use App\Enums\SuppressionReason;
use App\Models\SuppressedEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates a suppression row on first occurrence', function (): void {
    $event = new MailSuppressionEventData(
        email: 'jane@example.test',
        reason: SuppressionReason::HardBounce,
        occurredAt: now()->toImmutable(),
        providerEventType: 'failed',
    );

    app(RecordMailSuppression::class)($event);

    expect(SuppressedEmail::query()->where('email', 'jane@example.test')->count())->toBe(1);
});

it('is idempotent: a repeated event for the same address updates rather than duplicates', function (): void {
    $first = new MailSuppressionEventData('jane@example.test', SuppressionReason::HardBounce, now()->toImmutable(), 'failed');
    $second = new MailSuppressionEventData('jane@example.test', SuppressionReason::Complaint, now()->addMinute()->toImmutable(), 'complained');

    app(RecordMailSuppression::class)($first);
    app(RecordMailSuppression::class)($second);

    expect(SuppressedEmail::query()->where('email', 'jane@example.test')->count())->toBe(1)
        ->and(SuppressedEmail::query()->where('email', 'jane@example.test')->first()->reason)->toBe(SuppressionReason::Complaint);
});
