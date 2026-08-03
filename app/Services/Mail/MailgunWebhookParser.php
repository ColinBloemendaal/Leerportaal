<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Contracts\Mail\MailSuppressionWebhookParser;
use App\DataTransferObjects\Mail\MailSuppressionEventData;
use App\Enums\SuppressionReason;
use Carbon\CarbonImmutable;

/**
 * Reference implementation of MailSuppressionWebhookParser for Mailgun's
 * webhook format -- chosen over Postmark/SES for this first integration
 * since its HMAC signature scheme is simple and self-contained (no AWS SNS
 * subscription-confirmation handshake to also implement).
 *
 * @see https://documentation.mailgun.com/en/latest/user_manual.html#webhooks
 */
final readonly class MailgunWebhookParser implements MailSuppressionWebhookParser
{
    public function __construct(private string $signingKey) {}

    public function verifySignature(array $payload): bool
    {
        $signature = $payload['signature'] ?? null;

        if (! is_array($signature)) {
            return false;
        }

        $timestamp = $signature['timestamp'] ?? null;
        $token = $signature['token'] ?? null;
        $provided = $signature['signature'] ?? null;

        if (! is_string($timestamp) || ! is_string($token) || ! is_string($provided)) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.$token, $this->signingKey);

        return hash_equals($expected, $provided);
    }

    public function parse(array $payload): ?MailSuppressionEventData
    {
        $eventData = $payload['event-data'] ?? null;

        if (! is_array($eventData)) {
            return null;
        }

        $event = $eventData['event'] ?? null;
        $recipient = $eventData['recipient'] ?? null;
        $timestamp = $eventData['timestamp'] ?? null;

        if (! is_string($event) || ! is_string($recipient) || ! is_numeric($timestamp)) {
            return null;
        }

        $reason = match (true) {
            $event === 'failed' && ($eventData['severity'] ?? null) === 'permanent' => SuppressionReason::HardBounce,
            $event === 'complained' => SuppressionReason::Complaint,
            default => null,
        };

        if ($reason === null) {
            return null;
        }

        return new MailSuppressionEventData(
            email: $recipient,
            reason: $reason,
            occurredAt: CarbonImmutable::createFromTimestamp((float) $timestamp),
            providerEventType: $event,
        );
    }
}
