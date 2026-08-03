<?php

declare(strict_types=1);

use App\Mail\AssignmentOverdue;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds subject and content from the course', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create(['title' => 'Fire Safety 101']);
    $assignment = CourseAssignment::factory()
        ->for($reseller, 'reseller')
        ->for($course)
        ->withDeadline(now()->subDay())
        ->create();

    $mail = new AssignmentOverdue($assignment);

    expect($mail->envelope()->subject)->toBe('Overdue: Fire Safety 101')
        ->and($mail->content()->markdown)->toBe('emails.assignments.overdue');

    expect($mail->render())->toContain('Fire Safety 101');
});

it('throws when the assignment has no deadline', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create(['deadline_at' => null]);

    expect(fn () => new AssignmentOverdue($assignment))->toThrow(RuntimeException::class);
});
