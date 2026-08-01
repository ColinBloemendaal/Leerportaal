<?php

declare(strict_types=1);

use App\Actions\Mail\UpdateResellerMailTemplate;
use App\DataTransferObjects\Mail\UpdateResellerMailTemplateData;
use App\Enums\MailTemplateType;
use App\Models\Reseller;
use App\Models\ResellerMailTemplate;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function updateMailTemplateAction(): UpdateResellerMailTemplate
{
    return new UpdateResellerMailTemplate(app(TenantContext::class));
}

it('creates an override for a reseller that has none yet', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $template = (updateMailTemplateAction())(new UpdateResellerMailTemplateData(
        type: MailTemplateType::UserInvited,
        subject: 'Custom subject',
        bodyMarkdown: 'Custom body',
    ));

    expect($template?->reseller_id)->toBe($reseller->id)
        ->and($template?->subject)->toBe('Custom subject')
        ->and($template?->body_markdown)->toBe('Custom body');
});

it('updates the existing override in place rather than creating a duplicate', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $existing = ResellerMailTemplate::factory()->for($reseller, 'reseller')->create([
        'type' => MailTemplateType::UserInvited,
        'subject' => 'Old subject',
    ]);

    $template = (updateMailTemplateAction())(new UpdateResellerMailTemplateData(
        type: MailTemplateType::UserInvited,
        subject: 'New subject',
        bodyMarkdown: 'New body',
    ));

    expect($template?->id)->toBe($existing->id)
        ->and($template?->subject)->toBe('New subject')
        ->and(ResellerMailTemplate::query()->where('reseller_id', $reseller->id)->count())->toBe(1);
});

it('deletes the override and returns null when both fields are empty, resetting to default', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    ResellerMailTemplate::factory()->for($reseller, 'reseller')->create(['type' => MailTemplateType::UserInvited]);

    $result = (updateMailTemplateAction())(new UpdateResellerMailTemplateData(
        type: MailTemplateType::UserInvited,
        subject: null,
        bodyMarkdown: null,
    ));

    expect($result)->toBeNull()
        ->and(ResellerMailTemplate::query()->where('reseller_id', $reseller->id)->count())->toBe(0);
});
