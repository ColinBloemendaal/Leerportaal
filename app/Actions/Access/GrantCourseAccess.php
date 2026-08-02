<?php

declare(strict_types=1);

namespace App\Actions\Access;

use App\DataTransferObjects\Access\GrantCourseAccessData;
use App\Models\CourseAccessGrant;

final readonly class GrantCourseAccess
{
    public function __invoke(GrantCourseAccessData $data): CourseAccessGrant
    {
        return CourseAccessGrant::query()->create([
            'reseller_id' => $data->resellerId,
            'course_id' => $data->courseId,
            'course_category_id' => $data->courseCategoryId,
            'granted_by_user_id' => $data->grantedByUserId,
            'granted_at' => now(),
        ]);
    }
}
