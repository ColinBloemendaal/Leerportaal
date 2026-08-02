<?php

declare(strict_types=1);

use App\Enums\BlockTypeEnum;
use App\Models\Block;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds the full course -> module -> lesson -> block hierarchy', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();

    expect($module->course->id)->toBe($course->id)
        ->and($lesson->module->id)->toBe($module->id)
        ->and($block->lesson->id)->toBe($lesson->id)
        ->and($course->modules)->toHaveCount(1)
        ->and($module->lessons)->toHaveCount(1)
        ->and($lesson->blocks)->toHaveCount(1);
});

it('casts block type to the BlockTypeEnum and content to an array', function (): void {
    $block = Block::factory()->create([
        'type' => BlockTypeEnum::Callout,
        'content' => ['text' => 'Heads up', 'variant' => 'warning'],
    ]);

    $fresh = $block->fresh();

    expect($fresh?->type)->toBe(BlockTypeEnum::Callout)
        ->and($fresh?->content)->toBe(['text' => 'Heads up', 'variant' => 'warning']);
});

it('deletes modules, lessons, and blocks when their course is force-deleted', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();

    $course->forceDelete();

    expect(Module::find($module->id))->toBeNull()
        ->and(Lesson::find($lesson->id))->toBeNull()
        ->and(Block::find($block->id))->toBeNull();
});

it('soft deletes modules, lessons, and blocks independently', function (): void {
    $module = Module::factory()->create();
    $lesson = Lesson::factory()->create();
    $block = Block::factory()->create();

    $module->delete();
    $lesson->delete();
    $block->delete();

    expect(Module::find($module->id))->toBeNull()
        ->and(Module::withTrashed()->find($module->id))->not->toBeNull()
        ->and(Lesson::find($lesson->id))->toBeNull()
        ->and(Lesson::withTrashed()->find($lesson->id))->not->toBeNull()
        ->and(Block::find($block->id))->toBeNull()
        ->and(Block::withTrashed()->find($block->id))->not->toBeNull();
});
