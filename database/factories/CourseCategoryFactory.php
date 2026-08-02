<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CourseCategory;
use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseCategory>
 */
final class CourseCategoryFactory extends Factory
{
    protected $model = CourseCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Platform category by default -- pass ->reseller() or set
            // reseller_id explicitly for a reseller-owned one.
            'reseller_id' => null,
            'parent_id' => null,
            'name' => fake()->words(2, true),
            'order' => 0,
        ];
    }

    public function forReseller(?int $resellerId = null): self
    {
        return $this->state([
            'reseller_id' => $resellerId ?? Reseller::factory(),
        ]);
    }
}
