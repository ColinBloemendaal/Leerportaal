<?php

declare(strict_types=1);

use App\Models\Certificate;
use App\Models\Course;
use App\Services\Certificates\CertificateRenewalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function renewalService(): CertificateRenewalService
{
    return new CertificateRenewalService;
}

it('is not expired when there is no expiry date', function (): void {
    $certificate = Certificate::factory()->create(['expires_at' => null]);

    expect(renewalService()->isExpired($certificate))->toBeFalse();
});

it('is expired once the expiry date has passed', function (): void {
    $certificate = Certificate::factory()->expiringAt(now()->subDay())->create();

    expect(renewalService()->isExpired($certificate))->toBeTrue();
});

it('is expiring soon within the given window but not yet expired', function (): void {
    $certificate = Certificate::factory()->expiringAt(now()->addDays(10))->create();

    expect(renewalService()->isExpiringSoon($certificate, 30))->toBeTrue()
        ->and(renewalService()->isExpiringSoon($certificate, 5))->toBeFalse();
});

it('is not expiring soon once already expired', function (): void {
    $certificate = Certificate::factory()->expiringAt(now()->subDay())->create();

    expect(renewalService()->isExpiringSoon($certificate, 30))->toBeFalse();
});

it('returns the course itself as the latest variant when none exists', function (): void {
    $course = Course::factory()->create();

    expect(renewalService()->latestVariantOf($course)->is($course))->toBeTrue();
});

it('finds the newest variant by variant_year starting from the original course', function (): void {
    $original = Course::factory()->create(['variant_year' => 2024]);
    Course::factory()->create(['repeats_from_course_id' => $original->id, 'variant_year' => 2025]);
    $newest = Course::factory()->create(['repeats_from_course_id' => $original->id, 'variant_year' => 2026]);

    expect(renewalService()->latestVariantOf($original)->is($newest))->toBeTrue();
});

it('finds the newest variant even when starting from an older variant', function (): void {
    $original = Course::factory()->create(['variant_year' => 2024]);
    $older = Course::factory()->create(['repeats_from_course_id' => $original->id, 'variant_year' => 2025]);
    $newest = Course::factory()->create(['repeats_from_course_id' => $original->id, 'variant_year' => 2026]);

    expect(renewalService()->latestVariantOf($older)->is($newest))->toBeTrue();
});
