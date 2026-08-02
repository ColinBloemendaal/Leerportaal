<?php

declare(strict_types=1);

use App\Actions\Progress\MarkBlockCompleted;
use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use App\Services\Reports\CursistCourseEditionsReport;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function editionsReport(): CursistCourseEditionsReport
{
    return app(CursistCourseEditionsReport::class);
}

function courseEditionWithOneBlock(?int $repeatsFromCourseId, ?int $variantYear): array
{
    $course = Course::factory()->create([
        'repeats_from_course_id' => $repeatsFromCourseId,
        'variant_year' => $variantYear,
    ]);
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();

    return [$course, $block];
}

it('lists every edition in the lineage, including ones the cursist was never assigned', function (): void {
    [$original] = courseEditionWithOneBlock(null, 2024);
    [$variant2025] = courseEditionWithOneBlock($original->id, 2025);
    $user = User::factory()->create();

    $report = editionsReport()->forCursist($user, $original);

    expect($report)->toHaveCount(2)
        ->and($report->pluck('courseId')->all())->toBe([$original->id, $variant2025->id])
        ->and($report->pluck('wasAssigned')->all())->toBe([false, false]);
});

it('marks an edition as assigned and complete once the cursist finishes it', function (): void {
    [$original, $block] = courseEditionWithOneBlock(null, 2024);
    $user = User::factory()->create();
    $assignment = CourseAssignment::factory()->for($original)->for($user, 'user')->create();
    // CourseAssignment is TenantScoped -- the report query needs the
    // matching tenant resolved, same as any other cross-tenant-scoped
    // lookup in this codebase's tests.
    app(TenantContext::class)->set($assignment->reseller);
    app(MarkBlockCompleted::class)($assignment, $block);

    $report = editionsReport()->forCursist($user, $original);
    $row = $report->firstWhere('courseId', $original->id);

    expect($row->wasAssigned)->toBeTrue()
        ->and($row->isComplete)->toBeTrue()
        ->and($row->assignedAt)->not->toBeNull();
});

it('marks an assigned but unfinished edition as incomplete', function (): void {
    [$original] = courseEditionWithOneBlock(null, 2024);
    $user = User::factory()->create();
    $assignment = CourseAssignment::factory()->for($original)->for($user, 'user')->create();
    app(TenantContext::class)->set($assignment->reseller);

    $row = editionsReport()->forCursist($user, $original)->first();

    expect($row->wasAssigned)->toBeTrue()
        ->and($row->isComplete)->toBeFalse();
});

it('builds the same report starting from any variant in the lineage', function (): void {
    [$original] = courseEditionWithOneBlock(null, 2024);
    [$variant2025] = courseEditionWithOneBlock($original->id, 2025);
    $user = User::factory()->create();

    $fromOriginal = editionsReport()->forCursist($user, $original)->pluck('courseId')->all();
    $fromVariant = editionsReport()->forCursist($user, $variant2025)->pluck('courseId')->all();

    expect($fromVariant)->toBe($fromOriginal);
});
