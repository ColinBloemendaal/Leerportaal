<?php

declare(strict_types=1);

use App\Enums\SuppressionReason;
use App\Services\Mail\MailgunWebhookParser;

function mailgunPayload(array $eventData, string $signingKey = 'test-signing-key'): array
{
    $timestamp = '1723000000';
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

it('accepts a correctly signed payload', function (): void {
    $parser = new MailgunWebhookParser('test-signing-key');
    $payload = mailgunPayload(['event' => 'delivered', 'recipient' => 'jane@example.test', 'timestamp' => 1723000000]);

    expect($parser->verifySignature($payload))->toBeTrue();
});

it('rejects a payload signed with the wrong key', function (): void {
    $parser = new MailgunWebhookParser('test-signing-key');
    $payload = mailgunPayload(['event' => 'delivered'], signingKey: 'a-different-key');

    expect($parser->verifySignature($payload))->toBeFalse();
});

it('rejects a payload missing the signature block', function (): void {
    $parser = new MailgunWebhookParser('test-signing-key');

    expect($parser->verifySignature(['event-data' => ['event' => 'delivered']]))->toBeFalse();
});

it('parses a permanent failure as a hard bounce', function (): void {
    $parser = new MailgunWebhookParser('test-signing-key');
    $payload = mailgunPayload([
        'event' => 'failed',
        'severity' => 'permanent',
        'recipient' => 'jane@example.test',
        'timestamp' => 1723000000,
    ]);

    $event = $parser->parse($payload);

    expect($event)->not->toBeNull()
        ->and($event->email)->toBe('jane@example.test')
        ->and($event->reason)->toBe(SuppressionReason::HardBounce)
        ->and($event->providerEventType)->toBe('failed');
});

it('parses a complaint', function (): void {
    $parser = new MailgunWebhookParser('test-signing-key');
    $payload = mailgunPayload(['event' => 'complained', 'recipient' => 'jane@example.test', 'timestamp' => 1723000000]);

    $event = $parser->parse($payload);

    expect($event)->not->toBeNull()
        ->and($event->reason)->toBe(SuppressionReason::Complaint);
});

it('ignores a temporary failure', function (): void {
    $parser = new MailgunWebhookParser('test-signing-key');
    $payload = mailgunPayload([
        'event' => 'failed',
        'severity' => 'temporary',
        'recipient' => 'jane@example.test',
        'timestamp' => 1723000000,
    ]);

    expect($parser->parse($payload))->toBeNull();
});

it('ignores unrelated events like delivered or opened', function (): void {
    $parser = new MailgunWebhookParser('test-signing-key');

    expect($parser->parse(mailgunPayload(['event' => 'delivered', 'recipient' => 'a@b.test', 'timestamp' => 1723000000])))->toBeNull()
        ->and($parser->parse(mailgunPayload(['event' => 'opened', 'recipient' => 'a@b.test', 'timestamp' => 1723000000])))->toBeNull();
});
