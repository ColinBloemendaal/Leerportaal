<?php

declare(strict_types=1);

use App\Support\Money;

it('stores the given cents', function (): void {
    expect(Money::fromCents(1250)->cents)->toBe(1250);
});

it('considers equal amounts equal', function (): void {
    expect(Money::fromCents(500)->equals(Money::fromCents(500)))->toBeTrue()
        ->and(Money::fromCents(500)->equals(Money::fromCents(501)))->toBeFalse();
});

it('knows when it is zero', function (): void {
    expect(Money::fromCents(0)->isZero())->toBeTrue()
        ->and(Money::fromCents(1)->isZero())->toBeFalse();
});
