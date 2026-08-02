<?php

declare(strict_types=1);

use App\Models\CourseCategory;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('defaults to a platform category with no reseller', function (): void {
    $category = CourseCategory::factory()->create();

    expect($category->reseller_id)->toBeNull()
        ->and($category->reseller)->toBeNull();
});

it('belongs to a reseller when owned by one', function (): void {
    $reseller = Reseller::factory()->create();
    $category = CourseCategory::factory()->forReseller($reseller->id)->create();

    expect($category->reseller)->toBeInstanceOf(Reseller::class)
        ->and($category->reseller->id)->toBe($reseller->id);
});

it('nests under a parent category', function (): void {
    $parent = CourseCategory::factory()->create(['name' => 'Parent']);
    $child = CourseCategory::factory()->create(['name' => 'Child', 'parent_id' => $parent->id]);

    expect($child->parent?->id)->toBe($parent->id)
        ->and($parent->children->pluck('id')->all())->toBe([$child->id]);
});

it('nulls out the parent_id of children when the parent is force-deleted', function (): void {
    $parent = CourseCategory::factory()->create();
    $child = CourseCategory::factory()->create(['parent_id' => $parent->id]);

    $parent->forceDelete();

    expect($child->fresh()?->parent_id)->toBeNull();
});

it('soft deletes', function (): void {
    $category = CourseCategory::factory()->create();

    $category->delete();

    expect(CourseCategory::find($category->id))->toBeNull()
        ->and(CourseCategory::withTrashed()->find($category->id))->not->toBeNull();
});
