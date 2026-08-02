<?php

declare(strict_types=1);

use App\Models\Course;
use App\Services\Billing\AssignmentPricingService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function pricingService(): AssignmentPricingService
{
    return new AssignmentPricingService;
}

it('prices a catalog course at its fixed platform price', function (): void {
    $course = Course::factory()->create(['platform_price_cents' => 4999]);

    expect(pricingService()->priceFor($course)->cents)->toBe(4999);
});

it('prices a reseller course at 15 percent of the set price', function (): void {
    $course = Course::factory()->forReseller()->create(['reseller_set_price_cents' => 10000]);

    expect(pricingService()->priceFor($course)->cents)->toBe(1500);
});

it('floors a reseller course price at EUR 3.00', function (): void {
    $course = Course::factory()->forReseller()->create(['reseller_set_price_cents' => 1000]);

    expect(pricingService()->priceFor($course)->cents)->toBe(300);
});

it('treats a reseller course with no set price as free before the floor applies', function (): void {
    $course = Course::factory()->forReseller()->create(['reseller_set_price_cents' => null]);

    expect(pricingService()->priceFor($course)->cents)->toBe(300);
});

it('raises the price to a manual floor override when higher', function (): void {
    $course = Course::factory()->forReseller()->create([
        'reseller_set_price_cents' => 1000,
        'price_floor_override_cents' => 1000,
    ]);

    expect(pricingService()->priceFor($course)->cents)->toBe(1000);
});

it('does not lower the price when the floor override is below the computed price', function (): void {
    $course = Course::factory()->forReseller()->create([
        'reseller_set_price_cents' => 10000,
        'price_floor_override_cents' => 100,
    ]);

    expect(pricingService()->priceFor($course)->cents)->toBe(1500);
});

it('applies a floor override to a catalog course too', function (): void {
    $course = Course::factory()->create([
        'platform_price_cents' => 500,
        'price_floor_override_cents' => 2000,
    ]);

    expect(pricingService()->priceFor($course))->toEqual(Money::fromCents(2000));
});
