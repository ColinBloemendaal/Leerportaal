<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\CourseAssignment;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Certificate>
 */
final class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_assignment_id' => CourseAssignment::factory(),
            'verification_code' => Str::upper(Str::random(16)),
            'issued_at' => now(),
            'expires_at' => null,
            'pdf_disk' => 's3',
            'pdf_path' => 'certificates/'.fake()->uuid().'.pdf',
        ];
    }

    public function expiringAt(DateTimeInterface $expiresAt): self
    {
        return $this->state(fn (): array => ['expires_at' => $expiresAt]);
    }
}
