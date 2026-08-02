<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
final class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            // Platform (catalog) course by default -- pass ->forReseller()
            // for a reseller-owned one.
            'reseller_id' => null,
            'course_category_id' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'status' => CourseStatus::Draft,
            'available_locales' => ['nl'],
            'variant_year' => null,
            'repeats_from_course_id' => null,
            'reseller_set_price_cents' => null,
            'platform_price_cents' => null,
            'price_floor_override_cents' => null,
        ];
    }

    public function forReseller(?int $resellerId = null): self
    {
        return $this->state([
            'reseller_id' => $resellerId ?? Reseller::factory(),
        ]);
    }

    public function published(): self
    {
        return $this->state(['status' => CourseStatus::Published]);
    }

    public function inCategory(?int $categoryId = null): self
    {
        return $this->state([
            'course_category_id' => $categoryId ?? CourseCategory::factory(),
        ]);
    }
}
