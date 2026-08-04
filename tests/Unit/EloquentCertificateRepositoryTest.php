<?php

declare(strict_types=1);

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Repositories\Eloquent\EloquentCertificateRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('finds a certificate by its verification code, resolving the assignment without an ambient tenant', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->for($cursist, 'user')->create();
    $certificate = Certificate::factory()->for($assignment, 'courseAssignment')->create(['verification_code' => 'ABC123']);

    $found = app(EloquentCertificateRepository::class)->findByVerificationCode('ABC123');

    expect($found)->not->toBeNull()
        ->and($found->is($certificate))->toBeTrue();
});

it('finds every certificate for a user, with no ambient tenant', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    $ownAssignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->for($cursist, 'user')->create();
    $ownCertificate = Certificate::factory()->for($ownAssignment, 'courseAssignment')->create();

    $otherAssignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create();
    Certificate::factory()->for($otherAssignment, 'courseAssignment')->create();

    $found = app(EloquentCertificateRepository::class)->forUser($cursist->id);

    expect($found)->toHaveCount(1)
        ->and($found->first()->is($ownCertificate))->toBeTrue();
});
