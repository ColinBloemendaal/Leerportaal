<?php

declare(strict_types=1);

namespace App\Contracts\Mail;

use App\DataTransferObjects\Mail\MailSuppressionEventData;

/**
 * One implementation per email service provider's webhook format (see
 * App\Services\Mail\MailgunWebhookParser) -- kept behind an interface, per
 * CLAUDE.md §3a, so a future second provider (or a test fake) can swap in
 * without touching the controller that calls it.
 */
interface MailSuppressionWebhookParser
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifySignature(array $payload): bool;

    /**
     * Null when the event isn't suppression-relevant (delivered, opened,
     * clicked, etc.) -- not every provider webhook call is a bounce or
     * complaint.
     *
     * @param  array<string, mixed>  $payload
     */
    public function parse(array $payload): ?MailSuppressionEventData;
}
