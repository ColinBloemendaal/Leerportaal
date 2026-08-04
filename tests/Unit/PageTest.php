<?php

declare(strict_types=1);

use App\Enums\PageStatus;
use App\Enums\PageTemplate;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a reseller', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $page = Page::factory()->for($reseller)->create();

    expect($page->reseller->is($reseller))->toBeTrue();
});

it('has many blocks, ordered by their own order column', function (): void {
    $page = Page::factory()->create();
    app(TenantContext::class)->set($page->reseller);

    PageBlock::factory()->for($page)->create(['order' => 2]);
    PageBlock::factory()->for($page)->create(['order' => 0]);
    PageBlock::factory()->for($page)->create(['order' => 1]);

    expect($page->blocks->pluck('order')->all())->toBe([0, 1, 2]);
});

it('is only published when its status is published', function (): void {
    $draft = Page::factory()->create(['status' => PageStatus::Draft]);
    $published = Page::factory()->published()->create();

    expect($draft->isPublished())->toBeFalse()
        ->and($published->isPublished())->toBeTrue();
});

it('casts template and status', function (): void {
    $page = Page::factory()->create(['template' => PageTemplate::About]);

    expect($page->template)->toBe(PageTemplate::About)
        ->and($page->status)->toBe(PageStatus::Draft);
});

it('enforces one page per template per reseller', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    Page::factory()->for($reseller)->create(['template' => PageTemplate::Home]);

    expect(fn () => Page::factory()->for($reseller)->create(['template' => PageTemplate::Home]))
        ->toThrow(QueryException::class);
});
