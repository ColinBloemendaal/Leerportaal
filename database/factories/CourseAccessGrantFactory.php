<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseAccessGrant;
use App\Models\CourseCategory;
use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseAccessGrant>
 */
final class CourseAccessGrantFactory extends Factory
{
    protected $model = CourseAccessGrant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reseller_id' => Reseller::factory(),
            // Defaults to a course-targeted grant; use ->forCategory() for
            // the category-targeted variant (mutually exclusive with this).
            'course_id' => Course::factory(),
            'course_category_id' => null,
            'granted_by_user_id' => null,
            'granted_at' => now(),
            'revoked_at' => null,
        ];
    }

    public function forCategory(?int $categoryId = null): self
    {
        return $this->state([
            'course_id' => null,
            'course_category_id' => $categoryId ?? CourseCategory::factory(),
        ]);
    }

    public function revoked(): self
    {
        return $this->state(fn (): array => ['revoked_at' => now()]);
    }
}
