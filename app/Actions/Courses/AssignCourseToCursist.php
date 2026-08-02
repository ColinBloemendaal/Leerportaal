<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\DataTransferObjects\Courses\AssignCourseData;
use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Services\Billing\AssignmentPricingService;
use Illuminate\Database\ConnectionInterface;

final readonly class AssignCourseToCursist
{
    public function __construct(
        private AssignmentPricingService $pricing,
        private ConnectionInterface $db,
    ) {}

    public function __invoke(Course $course, AssignCourseData $data): CourseAssignment
    {
        return $this->db->transaction(function () use ($course, $data): CourseAssignment {
            return CourseAssignment::query()->create([
                'user_id' => $data->userId,
                'course_id' => $course->id,
                'assigned_by_user_id' => $data->assignedByUserId,
                'assigned_at' => now(),
                'price_cents' => $this->pricing->priceFor($course),
                'billing_state' => AssignmentBillingState::Pending,
            ]);
        });
    }
}
