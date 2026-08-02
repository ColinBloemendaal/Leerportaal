<?php

declare(strict_types=1);

use App\Actions\Courses\DuplicateCourse;
use App\Enums\CourseStatus;
use App\Models\Block;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Reseller;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function duplicateCourseAction(): DuplicateCourse
{
    return new DuplicateCourse(app(ConnectionInterface::class));
}

it('creates a new draft course with the same owner', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->forReseller($reseller->id)->published()->create(['slug' => 'my-course']);

    $copy = (duplicateCourseAction())($course);

    expect($copy->id)->not->toBe($course->id)
        ->and($copy->reseller_id)->toBe($reseller->id)
        ->and($copy->status)->toBe(CourseStatus::Draft)
        ->and($copy->version)->toBe(0)
        ->and($copy->published_at)->toBeNull()
        ->and($copy->slug)->toBe('my-course-copy');
});

it('keeps a platform course platform-owned when duplicated', function (): void {
    $course = Course::factory()->create();

    $copy = (duplicateCourseAction())($course);

    expect($copy->reseller_id)->toBeNull();
});

it('does not carry over variant/repeat fields', function (): void {
    $original = Course::factory()->create();
    $course = Course::factory()->create([
        'repeats_from_course_id' => $original->id,
        'variant_year' => 2027,
    ]);

    $copy = (duplicateCourseAction())($course);

    expect($copy->variant_year)->toBeNull()
        ->and($copy->repeats_from_course_id)->toBeNull();
});

it('appends "(Copy)" to the title in every locale', function (): void {
    $course = Course::factory()->create();
    $course->setTranslation('title', 'nl', 'Mijn cursus');
    $course->setTranslation('title', 'en', 'My course');
    $course->save();

    $copy = (duplicateCourseAction())($course);

    expect($copy->getTranslation('title', 'nl'))->toBe('Mijn cursus (Copy)')
        ->and($copy->getTranslation('title', 'en'))->toBe('My course (Copy)');
});

it('avoids a slug collision by appending an incrementing suffix', function (): void {
    $course = Course::factory()->create(['slug' => 'my-course']);
    Course::factory()->create(['slug' => 'my-course-copy']);

    $copy = (duplicateCourseAction())($course);

    expect($copy->slug)->toBe('my-course-copy-2');
});

it('copies the full module -> lesson -> block tree', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create(['title' => 'Module 1']);
    $lesson = Lesson::factory()->for($module)->create(['title' => 'Lesson 1']);
    Block::factory()->for($lesson)->create();

    $copy = (duplicateCourseAction())($course);

    $copyModule = $copy->modules()->sole();
    expect($copyModule->id)->not->toBe($module->id)
        ->and($copyModule->title)->toBe('Module 1');

    $copyLesson = $copyModule->lessons()->sole();
    expect($copyLesson->id)->not->toBe($lesson->id)
        ->and($copyLesson->title)->toBe('Lesson 1');

    expect($copyLesson->blocks()->count())->toBe(1);
});

it('leaves the original course and its content untouched', function (): void {
    $course = Course::factory()->create();
    Module::factory()->for($course)->create();

    (duplicateCourseAction())($course);

    // 2 courses (original + copy) and 2 modules (original + its copy) --
    // the original's own module count must still be exactly 1, not 2.
    expect(Course::query()->count())->toBe(2)
        ->and(Module::query()->count())->toBe(2)
        ->and($course->fresh()?->modules()->count())->toBe(1);
});
