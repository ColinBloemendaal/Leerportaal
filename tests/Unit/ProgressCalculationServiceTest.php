<?php

declare(strict_types=1);

use App\Actions\Progress\MarkBlockCompleted;
use App\Actions\Progress\MarkBlockViewed;
use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use App\Services\Progress\ProgressCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function progressService(): ProgressCalculationService
{
    return new ProgressCalculationService;
}

function buildCourseWithBlocks(int $lessonCount = 1, int $blocksPerLesson = 4): array
{
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create(['order' => 0]);
    $blocks = [];

    for ($l = 0; $l < $lessonCount; $l++) {
        $lesson = Lesson::factory()->for($module)->create(['order' => $l]);
        for ($b = 0; $b < $blocksPerLesson; $b++) {
            $blocks[] = Block::factory()->for($lesson)->create(['order' => $b]);
        }
    }

    return [$course, $module, $blocks];
}

it('reports 0 percent lesson completion with no progress', function (): void {
    [$course, $module, $blocks] = buildCourseWithBlocks(1, 4);
    $lesson = $blocks[0]->lesson;
    $assignment = CourseAssignment::factory()->create();

    expect(progressService()->lessonCompletionPercent($assignment, $lesson))->toBe(0.0);
});

it('reports partial lesson completion', function (): void {
    [$course, $module, $blocks] = buildCourseWithBlocks(1, 4);
    $lesson = $blocks[0]->lesson;
    $assignment = CourseAssignment::factory()->create();

    app(MarkBlockCompleted::class)($assignment, $blocks[0]);

    expect(progressService()->lessonCompletionPercent($assignment, $lesson))->toBe(25.0);
});

it('reports 100 percent when every block in the lesson is completed', function (): void {
    [$course, $module, $blocks] = buildCourseWithBlocks(1, 4);
    $lesson = $blocks[0]->lesson;
    $assignment = CourseAssignment::factory()->create();

    foreach ($blocks as $block) {
        app(MarkBlockCompleted::class)($assignment, $block);
    }

    expect(progressService()->lessonCompletionPercent($assignment, $lesson))->toBe(100.0);
});

it('aggregates module completion across lessons', function (): void {
    [$course, $module, $blocks] = buildCourseWithBlocks(2, 2);
    $assignment = CourseAssignment::factory()->create();

    // Complete 1 of 4 total blocks across the module's 2 lessons.
    app(MarkBlockCompleted::class)($assignment, $blocks[0]);

    expect(progressService()->moduleCompletionPercent($assignment, $module))->toBe(25.0);
});

it('aggregates course completion across the whole tree', function (): void {
    [$course, $module, $blocks] = buildCourseWithBlocks(2, 2);
    $assignment = CourseAssignment::factory()->create();

    foreach ($blocks as $block) {
        app(MarkBlockCompleted::class)($assignment, $block);
    }

    expect(progressService()->courseCompletionPercent($assignment, $course))->toBe(100.0);
});

it('resumes at the first block of the course when nothing has been viewed', function (): void {
    [$course, $module, $blocks] = buildCourseWithBlocks(1, 3);
    $assignment = CourseAssignment::factory()->create();

    $resumeBlock = progressService()->resumeBlock($assignment, $course);

    expect($resumeBlock?->id)->toBe($blocks[0]->id);
});

it('resumes at the most recently viewed block', function (): void {
    [$course, $module, $blocks] = buildCourseWithBlocks(1, 3);
    $assignment = CourseAssignment::factory()->create();

    app(MarkBlockViewed::class)($assignment, $blocks[0]);
    app(MarkBlockViewed::class)($assignment, $blocks[2]);

    $resumeBlock = progressService()->resumeBlock($assignment, $course);

    expect($resumeBlock?->id)->toBe($blocks[2]->id);
});

it('returns null resume point for a course with no blocks at all', function (): void {
    $course = Course::factory()->create();
    $assignment = CourseAssignment::factory()->create();

    expect(progressService()->resumeBlock($assignment, $course))->toBeNull();
});
