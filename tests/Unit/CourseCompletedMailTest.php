<?php

declare(strict_types=1);

use App\Mail\CourseCompleted;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds subject and content from the completed course', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create(['title' => 'Fire Safety 101']);
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create();

    $mail = new CourseCompleted($assignment);

    expect($mail->envelope()->subject)->toBe('Course completed: Fire Safety 101')
        ->and($mail->content()->markdown)->toBe('emails.assignments.completed')
        ->and($mail->content()->with['courseTitle'])->toBe('Fire Safety 101');

    expect($mail->render())->toContain('Fire Safety 101');
});
