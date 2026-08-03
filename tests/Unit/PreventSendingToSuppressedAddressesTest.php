<?php

declare(strict_types=1);

use App\Listeners\Mail\PreventSendingToSuppressedAddresses;
use App\Models\SuppressedEmail;
use App\Repositories\Eloquent\EloquentSuppressedEmailRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('cancels the send when the To address is suppressed', function (): void {
    SuppressedEmail::factory()->create(['email' => 'bounced@example.test']);
    $message = (new Email)->from('noreply@example.test')->to('bounced@example.test')->subject('x')->text('x');

    $listener = new PreventSendingToSuppressedAddresses(new EloquentSuppressedEmailRepository);

    expect($listener->handle(new MessageSending($message)))->toBeFalse();
});

it('allows the send through when no recipient is suppressed', function (): void {
    $message = (new Email)->from('noreply@example.test')->to('clean@example.test')->subject('x')->text('x');

    $listener = new PreventSendingToSuppressedAddresses(new EloquentSuppressedEmailRepository);

    expect($listener->handle(new MessageSending($message)))->toBeTrue();
});

it('also checks Cc and Bcc recipients', function (): void {
    SuppressedEmail::factory()->create(['email' => 'bounced@example.test']);
    $message = (new Email)->from('noreply@example.test')->to('clean@example.test')->cc('bounced@example.test')->subject('x')->text('x');

    $listener = new PreventSendingToSuppressedAddresses(new EloquentSuppressedEmailRepository);

    expect($listener->handle(new MessageSending($message)))->toBeFalse();
});
