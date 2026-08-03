<?php

declare(strict_types=1);

use App\Services\Billing\VatCalculator;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    config(['billing.platform_country_code' => 'NL', 'billing.vat_rate_percent' => 21]);
});

it('charges standard-rate VAT for a domestic reseller', function (): void {
    $result = (new VatCalculator)->calculate(10000, 'NL', null);

    expect($result->ratePercent)->toBe(21)
        ->and($result->vatCents)->toBe(2100)
        ->and($result->reverseCharge)->toBeFalse();
});

it('treats a reseller with no country code as domestic', function (): void {
    $result = (new VatCalculator)->calculate(10000, null, null);

    expect($result->ratePercent)->toBe(21)
        ->and($result->reverseCharge)->toBeFalse();
});

it('reverse-charges an EU reseller with a valid VAT ID', function (): void {
    $result = (new VatCalculator)->calculate(10000, 'DE', 'DE123456789');

    expect($result->ratePercent)->toBe(0)
        ->and($result->vatCents)->toBe(0)
        ->and($result->reverseCharge)->toBeTrue();
});

it('charges standard-rate VAT to an EU reseller with no VAT ID', function (): void {
    $result = (new VatCalculator)->calculate(10000, 'DE', null);

    expect($result->ratePercent)->toBe(21)
        ->and($result->reverseCharge)->toBeFalse();
});

it('charges standard-rate VAT to an EU reseller with an invalid VAT ID', function (): void {
    $result = (new VatCalculator)->calculate(10000, 'DE', 'not-a-vat-id!!');

    expect($result->ratePercent)->toBe(21)
        ->and($result->reverseCharge)->toBeFalse();
});

it('treats a non-EU reseller as outside the scope of VAT', function (): void {
    $result = (new VatCalculator)->calculate(10000, 'US', null);

    expect($result->ratePercent)->toBe(0)
        ->and($result->vatCents)->toBe(0)
        ->and($result->reverseCharge)->toBeTrue();
});
