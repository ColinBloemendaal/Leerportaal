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

    /**
     * Data merged into every branded notification's Content(with: ...) --
     * consumed by the "reseller" markdown theme (resources/views/vendor/mail/html/themes/reseller.blade.php)
     * and the shared header/footer partials (resources/views/emails/partials).
     * The logo is served through the existing public, unauthenticated
     * branding.logo route (App\Http\Controllers\ResellerBrandingController)
     * rather than the raw private-disk path: an email client fetches images
     * over HTTP with no session, unlike the certificate PDF's dompdf renderer
     * which can read the disk path directly.
     *
     * @return array<string, string|null>
     */
    private function brandingData(Reseller $reseller, ?ResellerTheme $resellerTheme): array
    {
        return [
            'resellerName' => $reseller->name,
            'primaryColor' => $resellerTheme === null ? '#0d6efd' : $resellerTheme->primary_color,
            'logoUrl' => $resellerTheme === null || $resellerTheme->logo_path === null
                ? null
                : route('branding.logo', $reseller->slug),
            'footerText' => $resellerTheme?->footer_text,
            'supportEmail' => $resellerTheme?->support_email,
            'termsUrl' => $resellerTheme?->terms_url,
            'privacyUrl' => $resellerTheme?->privacy_url,
        ];
    }
}
