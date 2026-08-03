<?php

declare(strict_types=1);

namespace App\Support\Billing;

/**
 * Structural/checksum-free format validation only -- confirms a VAT ID
 * matches its country's known pattern (2-letter prefix + the right shape
 * of digits/letters), not that it's actually registered. Real-time
 * verification against the EU's VIES registry is a separate, heavier
 * external integration (a live lookup service, the same shape as
 * PaymentGateway/MailSuppressionWebhookParser) that isn't built here --
 * documented gap, not silently assumed away.
 */
final class VatIdValidator
{
    /**
     * @var array<string, string>
     */
    private const PATTERNS = [
        'AT' => '/^ATU\d{8}$/',
        'BE' => '/^BE0\d{9}$/',
        'DE' => '/^DE\d{9}$/',
        'DK' => '/^DK\d{8}$/',
        'ES' => '/^ES[A-Z0-9]\d{7}[A-Z0-9]$/',
        'FI' => '/^FI\d{8}$/',
        'FR' => '/^FR[A-Z0-9]{2}\d{9}$/',
        'IE' => '/^IE\d{7}[A-Z]{1,2}$/',
        'IT' => '/^IT\d{11}$/',
        'NL' => '/^NL\d{9}B\d{2}$/',
        'PL' => '/^PL\d{10}$/',
        'PT' => '/^PT\d{9}$/',
        'SE' => '/^SE\d{12}$/',
    ];

    /**
     * Generic fallback for the EU countries above with no specific
     * pattern: the 2-letter country prefix followed by 2-12 alphanumeric
     * characters, per the general shape every EU VAT ID shares.
     */
    private const GENERIC_PATTERN = '/^[A-Z]{2}[A-Z0-9]{2,12}$/';

    public static function isValidFormat(string $vatId, string $countryCode): bool
    {
        $normalized = strtoupper(str_replace([' ', '-', '.'], '', $vatId));
        $pattern = self::PATTERNS[$countryCode] ?? self::GENERIC_PATTERN;

        return preg_match($pattern, $normalized) === 1;
    }
}
