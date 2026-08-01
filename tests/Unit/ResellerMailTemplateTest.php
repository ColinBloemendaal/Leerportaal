<?php

declare(strict_types=1);

use App\Enums\MailTemplateType;
use App\Models\Reseller;
use App\Models\ResellerMailTemplate;
use App\Tenancy\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('casts type to the MailTemplateType enum', function (): void {
    $template = ResellerMailTemplate::factory()->create(['type' => MailTemplateType::UserInvited]);

    expect($template->fresh()?->type)->toBe(MailTemplateType::UserInvited);
});

it('belongs to a reseller', function (): void {
    $template = ResellerMailTemplate::factory()->create();

    expect($template->reseller)->toBeInstanceOf(Reseller::class);
});

it('soft deletes', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $template = ResellerMailTemplate::factory()->for($reseller, 'reseller')->create();

    $template->delete();

    expect(ResellerMailTemplate::find($template->id))->toBeNull()
        ->and(ResellerMailTemplate::withTrashed()->find($template->id))->not->toBeNull();
});

it('enforces one override per reseller per type', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    ResellerMailTemplate::factory()->for($reseller, 'reseller')->create(['type' => MailTemplateType::UserInvited]);

    expect(fn () => ResellerMailTemplate::factory()->for($reseller, 'reseller')->create([
        'type' => MailTemplateType::UserInvited,
    ]))->toThrow(QueryException::class);
});
