<?php

declare(strict_types=1);

use App\Mail\CertificateIssued;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds subject and content from the completed course, with no ambient tenant', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create(['title' => 'Fire Safety 101']);
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create();
    $certificate = Certificate::factory()->for($assignment, 'courseAssignment')->create([
        'verification_code' => 'ABCD1234EFGH5678',
    ]);

    $mail = new CertificateIssued($certificate);

    expect($mail->envelope()->subject)->toBe('Certificate issued: Fire Safety 101')
        ->and($mail->content()->markdown)->toBe('emails.certificates.issued')
        ->and($mail->content()->with['verificationCode'])->toBe('ABCD1234EFGH5678')
        ->and($mail->content()->with['verificationUrl'])->toContain('ABCD1234EFGH5678');

    expect($mail->render())->toContain('ABCD1234EFGH5678');
});
