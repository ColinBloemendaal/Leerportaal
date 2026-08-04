<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageBlock>
 */
final class PageBlockFactory extends Factory
{
    protected $model = PageBlock::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'type' => 'text',
            'content' => [],
            'order' => 0,
            'visible' => true,
        ];
    }

    public function hidden(): self
    {
        return $this->state(fn (): array => ['visible' => false]);
    }
}
