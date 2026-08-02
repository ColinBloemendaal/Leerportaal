<?php

declare(strict_types=1);

use App\Actions\Progress\MarkBlockCompleted;
use App\Actions\Progress\MarkBlockViewed;
use App\Models\Block;
use App\Models\CourseAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('marks a block as viewed', function (): void {
    $assignment = CourseAssignment::factory()->create();
    $block = Block::factory()->create();

    $progress = app(MarkBlockViewed::class)($assignment, $block);

    expect($progress->last_viewed_at)->not->toBeNull()
        ->and($progress->completed_at)->toBeNull();
});

it('marks a block as completed, also updating last_viewed_at', function (): void {
    $assignment = CourseAssignment::factory()->create();
    $block = Block::factory()->create();

    $progress = app(MarkBlockCompleted::class)($assignment, $block);

    expect($progress->completed_at)->not->toBeNull()
        ->and($progress->last_viewed_at)->not->toBeNull();
});

it('does not duplicate rows when viewing the same block twice', function (): void {
    $assignment = CourseAssignment::factory()->create();
    $block = Block::factory()->create();

    app(MarkBlockViewed::class)($assignment, $block);
    app(MarkBlockViewed::class)($assignment, $block);

    expect($assignment->blockProgress()->count())->toBe(1);
});

it('viewing a block again does not un-complete it', function (): void {
    $assignment = CourseAssignment::factory()->create();
    $block = Block::factory()->create();

    app(MarkBlockCompleted::class)($assignment, $block);
    $progress = app(MarkBlockViewed::class)($assignment, $block);

    expect($progress->completed_at)->not->toBeNull();
});
