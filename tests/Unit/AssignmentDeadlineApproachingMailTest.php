<?php

declare(strict_types=1);

use App\Mail\AssignmentDeadlineApproaching;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds subject and content from the course and offset', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create(['title' => 'Fire Safety 101']);
    $assignment = CourseAssignment::factory()
        ->for($reseller, 'reseller')
        ->for($course)
        ->withDeadline(now()->addDays(7))
        ->create();

    $mail = new AssignmentDeadlineApproaching($assignment, 7);

    expect($mail->envelope()->subject)->toBe('Deadline approaching: Fire Safety 101')
        ->and($mail->content()->markdown)->toBe('emails.assignments.deadline-approaching')
        ->and($mail->content()->with['daysBefore'])->toBe(7);
});

it('throws when the assignment has no deadline', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create(['deadline_at' => null]);

    expect(fn () => new AssignmentDeadlineApproaching($assignment, 7))->toThrow(RuntimeException::class);
});
