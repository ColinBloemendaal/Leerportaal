<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\PageBlock;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a page', function (): void {
    $page = Page::factory()->create();
    app(TenantContext::class)->set($page->reseller);
    $block = PageBlock::factory()->for($page)->create();

    expect($block->page->is($page))->toBeTrue();
});

it('casts content to an array and visible to a boolean', function (): void {
    $block = PageBlock::factory()->create(['content' => ['heading' => 'Hello'], 'visible' => 0]);

    expect($block->content)->toBe(['heading' => 'Hello'])
        ->and($block->visible)->toBeFalse();
});

it('soft deletes', function (): void {
    $block = PageBlock::factory()->create();

    $block->delete();

    expect(PageBlock::find($block->id))->toBeNull()
        ->and(PageBlock::withTrashed()->find($block->id))->not->toBeNull();
});
