<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Repositories\Eloquent\EloquentCourseAssignmentRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('paginates assignments for the current reseller only, filtered by billing state', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $course = Course::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    CourseAssignment::factory()->for($course)->for($cursist, 'user')->create([
        'reseller_id' => $reseller->id,
        'billing_state' => AssignmentBillingState::Billed,
    ]);
    CourseAssignment::factory()->for($course)->for($cursist, 'user')->create([
        'reseller_id' => $reseller->id,
        'billing_state' => AssignmentBillingState::Pending,
    ]);
    $otherCursist = User::factory()->create(['reseller_id' => $otherReseller->id]);
    CourseAssignment::factory()->for($course)->for($otherCursist, 'user')->create([
        'reseller_id' => $otherReseller->id,
        'billing_state' => AssignmentBillingState::Billed,
    ]);

    $repository = app(EloquentCourseAssignmentRepository::class);
    $filters = new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: ['billing_state' => 'billed']);

    $result = $repository->paginate($filters);

    expect($result->total())->toBe(1);
});
