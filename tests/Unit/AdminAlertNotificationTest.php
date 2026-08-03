<?php

declare(strict_types=1);

use App\Enums\NotificationType;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('routes through mail and database channels with the given subject and message', function (): void {
    $admin = User::factory()->create();
    $notification = new AdminAlertNotification('Platform health alert', 'Something needs attention.');

    expect($notification->via($admin))->toBe(['mail', 'database']);

    $mail = $notification->toMail($admin);
    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Platform health alert');

    $payload = $notification->toDatabase($admin);
    expect($payload['type'])->toBe(NotificationType::AdminAlert->value)
        ->and($payload['subject'])->toBe('Platform health alert')
        ->and($payload['message'])->toBe('Something needs attention.');
});
