<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Block;
use App\Models\BlockProgress;
use App\Models\CourseAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockProgress>
 */
final class BlockProgressFactory extends Factory
{
    protected $model = BlockProgress::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_assignment_id' => CourseAssignment::factory(),
            'block_id' => Block::factory(),
            'last_viewed_at' => null,
            'completed_at' => null,
        ];
    }

    public function viewed(): self
    {
        return $this->state(fn (): array => ['last_viewed_at' => now()]);
    }

    public function completed(): self
    {
        return $this->state(fn (): array => ['completed_at' => now(), 'last_viewed_at' => now()]);
    }
}
