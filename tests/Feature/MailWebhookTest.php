<?php

declare(strict_types=1);

use App\Models\SuppressedEmail;

function signedMailgunPayload(array $eventData, string $signingKey): array
{
    $timestamp = (string) time();
    $token = 'a-random-token';

    return [
        'signature' => [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => hash_hmac('sha256', $timestamp.$token, $signingKey),
        ],
        'event-data' => $eventData,
    ];
}

it('records a suppression for a correctly signed bounce webhook', function (): void {
    config(['services.mailgun.webhook_signing_key' => 'a-real-signing-key']);

    $payload = signedMailgunPayload([
        'event' => 'failed',
        'severity' => 'permanent',
        'recipient' => 'bounced@example.test',
        'timestamp' => time(),
    ], 'a-real-signing-key');

    $this->postJson('/webhooks/mailgun', $payload)->assertOk();

    expect(SuppressedEmail::query()->where('email', 'bounced@example.test')->exists())->toBeTrue();
});

it('rejects a webhook with an invalid signature and records nothing', function (): void {
    config(['services.mailgun.webhook_signing_key' => 'a-real-signing-key']);

    $payload = signedMailgunPayload([
        'event' => 'failed',
        'severity' => 'permanent',
        'recipient' => 'bounced@example.test',
        'timestamp' => time(),
    ], 'the-wrong-key');

    $this->postJson('/webhooks/mailgun', $payload)->assertStatus(401);

    expect(SuppressedEmail::query()->where('email', 'bounced@example.test')->exists())->toBeFalse();
});

it('acknowledges a non-suppression event without creating a row', function (): void {
    config(['services.mailgun.webhook_signing_key' => 'a-real-signing-key']);

    $payload = signedMailgunPayload([
        'event' => 'delivered',
        'recipient' => 'happy@example.test',
        'timestamp' => time(),
    ], 'a-real-signing-key');

    $this->postJson('/webhooks/mailgun', $payload)->assertOk();

    expect(SuppressedEmail::query()->where('email', 'happy@example.test')->exists())->toBeFalse();
});
