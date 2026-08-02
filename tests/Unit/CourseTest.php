<?php

declare(strict_types=1);

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Reseller;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('defaults to a platform course with no reseller', function (): void {
    $course = Course::factory()->create();

    expect($course->reseller_id)->toBeNull()
        ->and($course->reseller)->toBeNull();
});

it('belongs to a reseller when owned by one', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->forReseller($reseller->id)->create();

    expect($course->reseller)->toBeInstanceOf(Reseller::class)
        ->and($course->reseller->id)->toBe($reseller->id);
});

it('casts status to the CourseStatus enum', function (): void {
    $course = Course::factory()->published()->create();

    expect($course->fresh()?->status)->toBe(CourseStatus::Published);
});

it('casts available_locales to an array', function (): void {
    $course = Course::factory()->create(['available_locales' => ['nl', 'en']]);

    expect($course->fresh()?->available_locales)->toBe(['nl', 'en']);
});

it('casts price columns to Money instances', function (): void {
    $course = Course::factory()->create(['platform_price_cents' => 1999]);

    $fresh = $course->fresh();

    expect($fresh?->platform_price_cents)->toBeInstanceOf(Money::class)
        ->and($fresh?->platform_price_cents->cents)->toBe(1999)
        ->and($fresh?->reseller_set_price_cents)->toBeNull();
});

it('persists a Money instance assigned directly to a price attribute', function (): void {
    $course = Course::factory()->create();
    $course->platform_price_cents = Money::fromCents(500);
    $course->save();

    expect($course->fresh()?->platform_price_cents->cents)->toBe(500);
});

it('links a variant back to the course it repeats', function (): void {
    $original = Course::factory()->create();
    $variant = Course::factory()->create(['repeats_from_course_id' => $original->id, 'variant_year' => 2027]);

    expect($variant->repeatsFrom?->id)->toBe($original->id)
        ->and($original->variants->pluck('id')->all())->toBe([$variant->id]);
});

it('soft deletes', function (): void {
    $course = Course::factory()->create();

    $course->delete();

    expect(Course::find($course->id))->toBeNull()
        ->and(Course::withTrashed()->find($course->id))->not->toBeNull();
});

it('stores title and description per locale', function (): void {
    $course = Course::factory()->create();
    $course->setTranslation('title', 'nl', 'Nederlandse titel');
    $course->setTranslation('title', 'en', 'English title');
    $course->setTranslation('description', 'nl', 'Nederlandse beschrijving');
    $course->setTranslation('description', 'en', 'English description');
    $course->save();

    $fresh = $course->fresh();

    expect($fresh?->getTranslation('title', 'nl'))->toBe('Nederlandse titel')
        ->and($fresh?->getTranslation('title', 'en'))->toBe('English title')
        ->and($fresh?->getTranslation('description', 'nl'))->toBe('Nederlandse beschrijving')
        ->and($fresh?->getTranslation('description', 'en'))->toBe('English description');
});

it('stores a plain string assignment under the current app locale', function (): void {
    $course = Course::factory()->create(['title' => 'Default locale title']);

    expect($course->fresh()?->getTranslation('title', app()->getLocale()))->toBe('Default locale title');
});
