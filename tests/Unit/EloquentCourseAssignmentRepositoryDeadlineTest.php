<?php

declare(strict_types=1);

use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Repositories\Eloquent\EloquentCourseAssignmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('lists deadlined, active assignments across every reseller, excluding revoked or deadline-less ones, with no ambient tenant', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    $due = CourseAssignment::factory()->for($resellerA, 'reseller')->withDeadline(now()->addDays(3))->create();
    CourseAssignment::factory()->for($resellerB, 'reseller')->withDeadline(now()->addDays(3))->revoked()->create();
    CourseAssignment::factory()->for($resellerB, 'reseller')->create(['deadline_at' => null]);
    $dueOtherReseller = CourseAssignment::factory()->for($resellerB, 'reseller')->withDeadline(now()->addDays(1))->create();

    $ids = app(EloquentCourseAssignmentRepository::class)->dueForDeadlineEvaluation()
        ->pluck('id')
        ->all();

    expect($ids)->toEqualCanonicalizing([$due->id, $dueOtherReseller->id]);
});
