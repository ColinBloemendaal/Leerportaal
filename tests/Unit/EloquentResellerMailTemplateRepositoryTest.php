<?php

declare(strict_types=1);

use App\Contracts\Repositories\ResellerMailTemplateRepository;
use App\Enums\MailTemplateType;
use App\Models\Reseller;
use App\Models\ResellerMailTemplate;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('finds the current reseller override for a type via ambient tenant context', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $template = ResellerMailTemplate::factory()->for($reseller, 'reseller')->create([
        'type' => MailTemplateType::UserInvited,
    ]);

    $found = app(ResellerMailTemplateRepository::class)->findForCurrentReseller(MailTemplateType::UserInvited);

    expect($found?->id)->toBe($template->id);
});

it('returns null for the current reseller when there is no override', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    expect(app(ResellerMailTemplateRepository::class)->findForCurrentReseller(MailTemplateType::UserInvited))
        ->toBeNull();
});

it('finds a specific reseller override by id without needing ambient tenant context', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();
    app(TenantContext::class)->set($resellerA);
    $templateA = ResellerMailTemplate::factory()->for($resellerA, 'reseller')->create([
        'type' => MailTemplateType::UserInvited,
    ]);
    app(TenantContext::class)->set($resellerB);
    ResellerMailTemplate::factory()->for($resellerB, 'reseller')->create(['type' => MailTemplateType::UserInvited]);

    $found = app(ResellerMailTemplateRepository::class)
        ->findForResellerAndType($resellerA->id, MailTemplateType::UserInvited);

    expect($found?->id)->toBe($templateA->id);
});
