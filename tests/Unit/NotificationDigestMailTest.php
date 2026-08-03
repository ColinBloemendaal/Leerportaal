<?php

declare(strict_types=1);

use App\Mail\NotificationDigest;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds a digest with every message and the reseller branding', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $mail = new NotificationDigest($user, ['You were assigned Fire Safety', 'Your certificate is ready']);

    expect($mail->envelope()->subject)->toBe('Your notification digest')
        ->and($mail->envelope()->from->name)->toBe('Acme Training')
        ->and($mail->content()->markdown)->toBe('emails.notifications.digest')
        ->and($mail->content()->with['messages'])->toHaveCount(2);
});

it('does not require a reseller for platform staff', function (): void {
    $user = User::factory()->platformStaff()->create();

    $mail = new NotificationDigest($user, ['An admin alert happened']);

    expect($mail->envelope()->subject)->toBe('Your notification digest');
});
