<?php

declare(strict_types=1);

use App\Enums\NotificationType;
use App\Models\AssignmentReminder;
use App\Models\Reseller;
use App\Repositories\Eloquent\EloquentAssignmentReminderRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('distinguishes sent reminders by type and offset', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    // reseller_id passed explicitly, matching the ambient TenantContext:
    // the factory's own default derives it from an independent
    // Reseller::factory() call, which would make this row invisible to
    // hasBeenSent()'s TenantScoped read otherwise.
    $reminder = AssignmentReminder::factory()->create(['reseller_id' => $reseller->id, 'days_before' => 7]);
    $repository = app(EloquentAssignmentReminderRepository::class);

    expect($repository->hasBeenSent($reminder->course_assignment_id, NotificationType::Deadline, 7))->toBeTrue()
        ->and($repository->hasBeenSent($reminder->course_assignment_id, NotificationType::Deadline, 1))->toBeFalse()
        ->and($repository->hasBeenSent($reminder->course_assignment_id, NotificationType::Overdue, null))->toBeFalse();
});
