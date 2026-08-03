<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationType;
use App\Models\AssignmentReminder;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentReminder>
 */
final class AssignmentReminderFactory extends Factory
{
    protected $model = AssignmentReminder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reseller_id' => Reseller::factory(),
            'course_assignment_id' => CourseAssignment::factory(),
            'type' => NotificationType::Deadline,
            'days_before' => 7,
            'sent_at' => now(),
        ];
    }

    public function overdue(): self
    {
        return $this->state(fn (): array => ['type' => NotificationType::Overdue, 'days_before' => null]);
    }
}
