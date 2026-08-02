<?php

declare(strict_types=1);

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows a valid certificate by its verification code', function (): void {
    $course = Course::factory()->create();
    $cursist = User::factory()->create(['name' => 'Jane Cursist']);
    $assignment = CourseAssignment::factory()->for($course)->for($cursist, 'user')->create();
    $certificate = Certificate::factory()->for($assignment, 'courseAssignment')->create([
        'verification_code' => 'ABCD1234EFGH5678',
    ]);

    $this->get('/certificates/verify/ABCD1234EFGH5678')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Certificates/Verify')
            ->where('certificate.data.recipient_name', 'Jane Cursist')
            ->where('certificate.data.verification_code', $certificate->verification_code));
});

it('shows nothing for an unknown verification code', function (): void {
    $this->get('/certificates/verify/DOES-NOT-EXIST')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Certificates/Verify')
            ->where('certificate', null));
});
