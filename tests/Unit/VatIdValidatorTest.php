<?php

declare(strict_types=1);

use App\Support\Billing\VatIdValidator;

it('accepts a correctly formatted Dutch VAT ID', function (): void {
    expect(VatIdValidator::isValidFormat('NL123456789B01', 'NL'))->toBeTrue();
});

it('accepts a correctly formatted German VAT ID', function (): void {
    expect(VatIdValidator::isValidFormat('DE123456789', 'DE'))->toBeTrue();
});

it('rejects a malformed VAT ID for a country with a specific pattern', function (): void {
    expect(VatIdValidator::isValidFormat('NL123', 'NL'))->toBeFalse()
        ->and(VatIdValidator::isValidFormat('DE12', 'DE'))->toBeFalse();
});

it('is tolerant of spaces and dashes', function (): void {
    expect(VatIdValidator::isValidFormat('NL 1234 5678 9 B01', 'NL'))->toBeTrue();
});

it('falls back to a generic pattern for a country with no specific rule', function (): void {
    expect(VatIdValidator::isValidFormat('LU12345678', 'LU'))->toBeTrue()
        ->and(VatIdValidator::isValidFormat('LU', 'LU'))->toBeFalse();
});
