<?php

declare(strict_types=1);

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\Gdpr\DataSubjectExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('composes every piece of a user\'s own data, regardless of ambient tenant', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    $course = Course::factory()->create(['title' => 'Fire Safety 101']);
    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Jane Cursist', 'email' => 'jane@example.test']);

    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->for($cursist, 'user')->create();
    $quiz = Quiz::factory()->create();
    QuizAttempt::factory()->for($quiz)->for($cursist, 'user')->create();
    Certificate::factory()->for($assignment, 'courseAssignment')->create(['verification_code' => 'ABC123']);
    $cursist->notify(new WelcomeNotification);

    $data = app(DataSubjectExportService::class)->forUser($cursist);

    expect($data['profile']['name'])->toBe('Jane Cursist')
        ->and($data['profile']['email'])->toBe('jane@example.test')
        ->and($data['profile']['reseller'])->toBe('Acme Training')
        ->and($data['course_assignments'])->toHaveCount(1)
        ->and($data['course_assignments'][0]['course_title'])->toBe('Fire Safety 101')
        ->and($data['quiz_attempts'])->toHaveCount(1)
        ->and($data['certificates'])->toHaveCount(1)
        ->and($data['certificates'][0]['verification_code'])->toBe('ABC123')
        ->and($data['notifications'])->toHaveCount(1);
});

it('includes revoked assignments, unlike the cursist dashboard\'s own read', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->for($cursist, 'user')->revoked()->create();

    $data = app(DataSubjectExportService::class)->forUser($cursist);

    expect($data['course_assignments'])->toHaveCount(1);
});
