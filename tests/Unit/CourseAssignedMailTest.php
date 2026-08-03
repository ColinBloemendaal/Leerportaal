<?php

declare(strict_types=1);

use App\Mail\CourseAssigned;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds a branded subject and content from the assigned course', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create(['title' => 'Fire Safety 101']);
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    $assignment = CourseAssignment::factory()->create([
        'reseller_id' => $reseller->id,
        'course_id' => $course->id,
        'user_id' => $cursist->id,
        'deadline_at' => null,
    ]);

    $mail = new CourseAssigned($assignment);

    expect($mail->envelope()->subject)->toBe('New course assigned: Fire Safety 101')
        ->and($mail->content()->markdown)->toBe('emails.assignments.assigned')
        ->and($mail->content()->with['courseTitle'])->toBe('Fire Safety 101')
        ->and($mail->content()->with['deadlineAt'])->toBeNull();
});

it('includes a formatted deadline when one is set', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    $assignment = CourseAssignment::factory()->create([
        'reseller_id' => $reseller->id,
        'course_id' => $course->id,
        'user_id' => $cursist->id,
        'deadline_at' => now()->addDays(5),
    ]);

    $mail = new CourseAssigned($assignment);

    expect($mail->content()->with['deadlineAt'])->toBe($assignment->deadline_at->toFormattedDateString());
});
