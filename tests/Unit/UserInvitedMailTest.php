<?php

declare(strict_types=1);

use App\Enums\MailTemplateType;
use App\Mail\UserInvited;
use App\Models\Reseller;
use App\Models\ResellerMailTemplate;
use App\Models\ResellerTheme;
use App\Models\UserInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailables\Address;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('falls back to the reseller name and no reply-to when there is no theme', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    $invite = UserInvite::factory()->for($reseller, 'reseller')->create();

    $envelope = (new UserInvited($invite))->envelope();

    expect($envelope->from)->toBeInstanceOf(Address::class)
        ->and($envelope->from->address)->toBe(config('mail.from.address'))
        ->and($envelope->from->name)->toBe('Acme Training')
        ->and($envelope->replyTo)->toBe([]);
});

it('uses the theme sender name and reply-to when set', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    ResellerTheme::factory()->for($reseller, 'reseller')->create([
        'sender_name' => 'Acme Support Team',
        'reply_to_email' => 'support@acme.example',
    ]);
    $invite = UserInvite::factory()->for($reseller, 'reseller')->create();

    $envelope = (new UserInvited($invite))->envelope();

    expect($envelope->from->name)->toBe('Acme Support Team')
        ->and($envelope->from->address)->toBe(config('mail.from.address'))
        ->and($envelope->replyTo)->toHaveCount(1)
        ->and($envelope->replyTo[0]->address)->toBe('support@acme.example');
});

it('never sends from a reseller-supplied address, only the platform one', function (): void {
    $reseller = Reseller::factory()->create();
    ResellerTheme::factory()->for($reseller, 'reseller')->create(['sender_name' => 'Acme']);
    $invite = UserInvite::factory()->for($reseller, 'reseller')->create();

    $envelope = (new UserInvited($invite))->envelope();

    expect($envelope->from->address)->toBe(config('mail.from.address'));
});

it('uses the default subject and Blade view when there is no template override', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    $invite = UserInvite::factory()->for($reseller, 'reseller')->create();

    $mail = new UserInvited($invite);

    expect($mail->envelope()->subject)->toBe('Acme Training invited you to Leerportaal')
        ->and($mail->content()->markdown)->toBe('emails.invites.invited')
        ->and($mail->content()->htmlString)->toBeNull();

    expect($mail->render())->toContain('Acme Training');
});

it('uses the reseller template override subject and body when one exists', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    ResellerMailTemplate::factory()->for($reseller, 'reseller')->create([
        'type' => MailTemplateType::UserInvited,
        'subject' => 'A note from {{ reseller_name }}',
        'body_markdown' => 'Hi {{ invitee_name }}, [click here]({{ accept_url }}).',
    ]);
    $invite = UserInvite::factory()->for($reseller, 'reseller')->create(['name' => 'Bob']);

    $mail = new UserInvited($invite);

    expect($mail->envelope()->subject)->toBe('A note from Acme Training');

    $content = $mail->content();
    expect($content->markdown)->toBeNull()
        ->and($content->htmlString)->toContain('Hi Bob')
        ->and($content->htmlString)->toContain('<a href=');
});
