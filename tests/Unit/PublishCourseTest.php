<?php

declare(strict_types=1);

use App\Actions\Courses\PublishCourse;
use App\Enums\CourseStatus;
use App\Exceptions\IncompleteCourseTranslationsException;
use App\Exceptions\InvalidCourseStatusTransitionException;
use App\Models\Course;
use App\Services\Translation\TranslationCompletenessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function publishCourseAction(): PublishCourse
{
    return new PublishCourse(new TranslationCompletenessService);
}

it('publishes an in-review course with complete translations, incrementing the version', function (): void {
    $course = Course::factory()->create(['status' => CourseStatus::InReview, 'available_locales' => ['nl']]);
    $course->setTranslation('title', 'nl', 'Titel');
    $course->setTranslation('description', 'nl', 'Beschrijving');
    $course->save();

    $result = (publishCourseAction())($course);

    expect($result->status)->toBe(CourseStatus::Published)
        ->and($result->version)->toBe(1)
        ->and($result->published_at)->not->toBeNull();
});

it('increments the version on each republish', function (): void {
    $course = Course::factory()->create([
        'status' => CourseStatus::InReview,
        'available_locales' => ['nl'],
        'version' => 3,
    ]);
    $course->setTranslation('title', 'nl', 'Titel');
    $course->setTranslation('description', 'nl', 'Beschrijving');
    $course->save();

    $result = (publishCourseAction())($course);

    expect($result->version)->toBe(4);
});

it('rejects publishing a draft course', function (): void {
    $course = Course::factory()->create(['status' => CourseStatus::Draft]);

    expect(fn () => (publishCourseAction())($course))
        ->toThrow(InvalidCourseStatusTransitionException::class);
});

it('rejects publishing when a locale has incomplete translations', function (): void {
    $course = Course::factory()->create(['status' => CourseStatus::InReview, 'available_locales' => ['nl', 'en']]);
    $course->setTranslation('title', 'nl', 'Titel');
    $course->setTranslation('description', 'nl', 'Beschrijving');
    // The factory's plain-string title/description already populated the
    // app's default locale (typically "en") via HasTranslations -- clear
    // it explicitly so this locale is genuinely incomplete.
    $course->forgetTranslation('title', 'en');
    $course->forgetTranslation('description', 'en');
    $course->save();

    expect(fn () => (publishCourseAction())($course))
        ->toThrow(IncompleteCourseTranslationsException::class);

    expect($course->fresh()?->status)->toBe(CourseStatus::InReview);
});
