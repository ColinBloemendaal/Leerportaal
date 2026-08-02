<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseAssignment>
 */
final class CourseAssignmentFactory extends Factory
{
    protected $model = CourseAssignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reseller_id' => Reseller::factory(),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'assigned_by_user_id' => null,
            'assigned_at' => now(),
            'first_opened_at' => null,
            'revoked_at' => null,
            'billing_state' => AssignmentBillingState::Pending,
        ];
    }

    public function opened(): self
    {
        return $this->state(fn (): array => ['first_opened_at' => now()]);
    }

    public function revoked(): self
    {
        return $this->state(fn (): array => ['revoked_at' => now()]);
    }
}
