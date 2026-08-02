<?php

declare(strict_types=1);

use App\Actions\Courses\ReorderModules;
use App\DataTransferObjects\Courses\ReorderItemsData;
use App\Exceptions\InvalidOrderingException;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('reassigns module order to match the given sequence', function (): void {
    $course = Course::factory()->create();
    $first = Module::factory()->for($course)->create(['order' => 0]);
    $second = Module::factory()->for($course)->create(['order' => 1]);
    $third = Module::factory()->for($course)->create(['order' => 2]);

    app(ReorderModules::class)($course, new ReorderItemsData([$third->id, $first->id, $second->id]));

    expect($third->fresh()->order)->toBe(0)
        ->and($first->fresh()->order)->toBe(1)
        ->and($second->fresh()->order)->toBe(2);
});

it('rejects a reorder that omits a sibling', function (): void {
    $course = Course::factory()->create();
    $first = Module::factory()->for($course)->create(['order' => 0]);
    Module::factory()->for($course)->create(['order' => 1]);

    app(ReorderModules::class)($course, new ReorderItemsData([$first->id]));
})->throws(InvalidOrderingException::class);

it('rejects a reorder that includes an id from another course', function (): void {
    $course = Course::factory()->create();
    $first = Module::factory()->for($course)->create(['order' => 0]);
    $foreign = Module::factory()->create(['order' => 0]);

    app(ReorderModules::class)($course, new ReorderItemsData([$foreign->id, $first->id]));
})->throws(InvalidOrderingException::class);

it('does not touch modules belonging to a different course', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create(['order' => 0]);

    $otherCourse = Course::factory()->create();
    $otherModule = Module::factory()->for($otherCourse)->create(['order' => 0]);

    app(ReorderModules::class)($course, new ReorderItemsData([$module->id]));

    expect($otherModule->fresh()->order)->toBe(0);
});
