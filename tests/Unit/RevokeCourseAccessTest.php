<?php

declare(strict_types=1);

use App\Actions\Access\RevokeCourseAccess;
use App\Models\Certificate;
use App\Models\CourseAccessGrant;
use App\Models\CourseAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('sets revoked_at without deleting the grant', function (): void {
    $grant = CourseAccessGrant::factory()->create();

    $revoked = app(RevokeCourseAccess::class)($grant);

    expect($revoked->revoked_at)->not->toBeNull()
        ->and($revoked->isRevoked())->toBeTrue()
        ->and(CourseAccessGrant::query()->withoutTenantScope()->find($grant->id))->not->toBeNull();
});

it('does not touch existing assignments, progress, or certificates when access is revoked', function (): void {
    $grant = CourseAccessGrant::factory()->create();
    $assignment = CourseAssignment::factory()->create(['reseller_id' => $grant->reseller_id]);
    $certificate = Certificate::factory()->create(['course_assignment_id' => $assignment->id]);

    app(RevokeCourseAccess::class)($grant);

    // CourseAccessGrant has no relationship to any of these tables at
    // all -- this proves that by construction, not just by absence of a
    // cascade rule, per this phase's explicit requirement that revoking
    // access must never delete a cursist's progress or certificates.
    expect(CourseAssignment::query()->withoutTenantScope()->find($assignment->id))->not->toBeNull();
    expect($assignment->fresh()->revoked_at)->toBeNull();
    expect(Certificate::query()->find($certificate->id))->not->toBeNull();
});
