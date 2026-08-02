<?php

declare(strict_types=1);

use App\Models\Course;
use App\Services\Translation\TranslationCompletenessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = new TranslationCompletenessService;
});

it('is complete when every translatable field has a non-empty value for the locale', function (): void {
    $course = Course::factory()->create();
    $course->setTranslation('title', 'nl', 'Titel');
    $course->setTranslation('description', 'nl', 'Beschrijving');
    $course->save();

    expect($this->service->isComplete($course, 'nl'))->toBeTrue()
        ->and($this->service->missingFields($course, 'nl'))->toBe([]);
});

it('is incomplete when a translatable field is missing for the locale', function (): void {
    $course = Course::factory()->create();
    $course->setTranslation('title', 'nl', 'Titel');
    $course->save();

    expect($this->service->isComplete($course, 'nl'))->toBeFalse()
        ->and($this->service->missingFields($course, 'nl'))->toBe(['description']);
});

it('is incomplete when a translatable field is set but blank for the locale', function (): void {
    $course = Course::factory()->create();
    $course->setTranslation('title', 'nl', '   ');
    $course->setTranslation('description', 'nl', 'Beschrijving');
    $course->save();

    expect($this->service->isComplete($course, 'nl'))->toBeFalse()
        ->and($this->service->missingFields($course, 'nl'))->toBe(['title']);
});

it('is incomplete when the locale has no translations at all', function (): void {
    $course = Course::factory()->create();
    $course->setTranslation('title', 'en', 'Title');
    $course->setTranslation('description', 'en', 'Description');
    $course->save();

    expect($this->service->isComplete($course, 'nl'))->toBeFalse()
        ->and($this->service->missingFields($course, 'nl'))->toBe(['title', 'description']);
});
