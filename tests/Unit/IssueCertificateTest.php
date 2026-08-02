<?php

declare(strict_types=1);

use App\Actions\Certificates\IssueCertificate;
use App\Actions\Progress\MarkBlockCompleted;
use App\Exceptions\CourseAssignmentNotCompleteException;
use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function completedAssignment(): array
{
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();
    $assignment = CourseAssignment::factory()->for($course)->create();

    app(MarkBlockCompleted::class)($assignment, $block);

    return [$assignment, $course];
}

it('refuses to issue a certificate for an incomplete assignment', function (): void {
    $course = Course::factory()->create();
    Module::factory()->for($course)->create();
    $assignment = CourseAssignment::factory()->for($course)->create();

    app(IssueCertificate::class)($assignment);
})->throws(CourseAssignmentNotCompleteException::class);

it('issues a certificate with a unique verification code and stores the PDF', function (): void {
    Storage::fake('s3');
    [$assignment] = completedAssignment();

    $certificate = app(IssueCertificate::class)($assignment);

    expect($certificate->course_assignment_id)->toBe($assignment->id)
        ->and($certificate->verification_code)->toHaveLength(16)
        ->and($certificate->issued_at)->not->toBeNull()
        ->and($certificate->pdf_path)->toBe("certificates/{$certificate->verification_code}.pdf");

    Storage::disk('s3')->assertExists($certificate->pdf_path);
});

it('generates a different verification code for a different assignment', function (): void {
    Storage::fake('s3');
    [$assignmentA] = completedAssignment();
    [$assignmentB] = completedAssignment();

    $certificateA = app(IssueCertificate::class)($assignmentA);
    $certificateB = app(IssueCertificate::class)($assignmentB);

    expect($certificateA->verification_code)->not->toBe($certificateB->verification_code);
});
