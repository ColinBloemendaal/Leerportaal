<?php

declare(strict_types=1);

namespace App\Mail\Concerns;

use App\Models\Reseller;
use App\Models\ResellerTheme;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Shared by Mailables that send on behalf of a specific reseller.
 *
 * The underlying "from" address is deliberately always the platform's own
 * verified sending address, never a reseller-supplied one: this app has no
 * per-reseller domain verification (SPF/DKIM), so sending "from" an
 * unverified address would hurt deliverability or get flagged as spoofed.
 * What resellers can customize is the display name recipients see, and
 * where replies go.
 */
trait HasResellerBranding
{
    private function brandedEnvelope(Reseller $reseller, ?ResellerTheme $resellerTheme, string $subject): Envelope
    {
        $senderName = $resellerTheme === null
            ? $reseller->name
            : ($resellerTheme->sender_name ?? $reseller->name);

        $replyTo = $resellerTheme !== null && $resellerTheme->reply_to_email !== null
            ? [$resellerTheme->reply_to_email]
            : [];

        return new Envelope(
            from: new Address((string) config('mail.from.address'), $senderName),
            replyTo: $replyTo,
            subject: $subject,
        );
    }
}
