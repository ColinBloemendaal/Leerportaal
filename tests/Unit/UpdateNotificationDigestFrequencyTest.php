<?php

declare(strict_types=1);

use App\Actions\Notifications\UpdateNotificationDigestFrequency;
use App\Enums\DigestFrequency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('updates the user\'s digest frequency', function (): void {
    $user = User::factory()->create(['notification_digest_frequency' => DigestFrequency::Immediate]);

    app(UpdateNotificationDigestFrequency::class)($user->id, DigestFrequency::Weekly);

    expect($user->fresh()->notification_digest_frequency)->toBe(DigestFrequency::Weekly);
});
