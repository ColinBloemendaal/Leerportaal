<?php

declare(strict_types=1);

use App\Enums\MailTemplateType;
use App\Models\Reseller;
use App\Models\ResellerMailTemplate;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    $this->reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    $this->user = User::factory()->create(['reseller_id' => $this->reseller->id]);
    app(TenantContext::class)->set($this->reseller);
});

it('lists every notification type with its override status', function (): void {
    ResellerMailTemplate::factory()->for($this->reseller, 'reseller')->create(['type' => MailTemplateType::UserInvited]);

    $this->actingAs($this->user)
        ->get('/settings/email-templates')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Settings/EmailTemplates/Index')
            ->where('types.0.type', MailTemplateType::UserInvited->value)
            ->where('types.0.overridden', true));
});

it('shows no override on the edit page when the reseller has not customized it', function (): void {
    $this->actingAs($this->user)
        ->get('/settings/email-templates/user_invited')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Settings/EmailTemplates/Edit')
            ->where('override', null));
});

it('shows the persisted override on the edit page when one exists', function (): void {
    ResellerMailTemplate::factory()->for($this->reseller, 'reseller')->create([
        'type' => MailTemplateType::UserInvited,
        'subject' => 'Custom subject',
    ]);

    $this->actingAs($this->user)
        ->get('/settings/email-templates/user_invited')
        ->assertInertia(fn (AssertableInertia $page) => $page->where('override.data.subject', 'Custom subject'));
});

it('creates an override through the full FormRequest -> DTO -> Action -> Repository chain', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/email-templates/user_invited', [
            'subject' => 'Welcome to {{ reseller_name }}',
            'body_markdown' => 'Hi {{ invitee_name }}!',
        ])
        ->assertRedirect('/settings/email-templates/user_invited');

    $this->assertDatabaseHas('reseller_mail_templates', [
        'reseller_id' => $this->reseller->id,
        'type' => MailTemplateType::UserInvited->value,
        'subject' => 'Welcome to {{ reseller_name }}',
    ]);
});

it('resets to default when both fields are submitted empty', function (): void {
    ResellerMailTemplate::factory()->for($this->reseller, 'reseller')->create(['type' => MailTemplateType::UserInvited]);

    $this->actingAs($this->user)
        ->put('/settings/email-templates/user_invited', ['subject' => '', 'body_markdown' => ''])
        ->assertRedirect('/settings/email-templates/user_invited');

    $this->assertSoftDeleted('reseller_mail_templates', ['reseller_id' => $this->reseller->id]);
});

it('rejects a subject without a body', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/email-templates/user_invited', ['subject' => 'Only a subject'])
        ->assertSessionHasErrors('body_markdown');
});

it('rejects a body that exceeds the hard character limit', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/email-templates/user_invited', [
            'subject' => 'Subject',
            'body_markdown' => str_repeat('a', 20001),
        ])
        ->assertSessionHasErrors('body_markdown');
});

it('rejects a body that attempts to break out of the wrapping markup', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/email-templates/user_invited', [
            'subject' => 'Subject',
            'body_markdown' => '<script>alert(1)</script>',
        ])
        ->assertSessionHasErrors('body_markdown');
});

it('denies platform staff from viewing or updating email templates', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/settings/email-templates')->assertForbidden();
    $this->actingAs($staff)->get('/settings/email-templates/user_invited')->assertForbidden();
    $this->actingAs($staff)->put('/settings/email-templates/user_invited', [])->assertForbidden();
});

it('redirects guests to login', function (): void {
    $this->get('/settings/email-templates')->assertRedirect('/login');
});
