<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BlockTypeEnum;
use App\Models\Block;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Block>
 */
final class BlockFactory extends Factory
{
    protected $model = Block::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'type' => BlockTypeEnum::RichText,
            'content' => ['html' => '<p>'.fake()->sentence().'</p>'],
            'order' => 0,
        ];
    }
}
