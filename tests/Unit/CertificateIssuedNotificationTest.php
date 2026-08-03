<?php

declare(strict_types=1);

use App\Enums\NotificationType;
use App\Mail\CertificateIssued;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\CertificateIssuedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('routes through mail and database channels, and builds a mailable addressed to the cursist', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'email' => 'cursist@example.test']);
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->for($cursist, 'user')->create();
    $certificate = Certificate::factory()->for($assignment, 'courseAssignment')->create();

    $notification = new CertificateIssuedNotification($certificate);

    expect($notification->via($cursist))->toBe(['mail', 'database']);

    $mail = $notification->toMail($cursist);
    expect($mail)->toBeInstanceOf(CertificateIssued::class)
        ->and($mail->to[0]['address'])->toBe('cursist@example.test');

    $payload = $notification->toDatabase($cursist);
    expect($payload['type'])->toBe(NotificationType::Certificate->value)
        ->and($payload['certificate_id'])->toBe($certificate->id)
        ->and($payload['verification_code'])->toBe($certificate->verification_code);
});
