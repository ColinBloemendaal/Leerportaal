<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('shows the verify-email notice for an unverified user', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/email/verify')
        ->assertOk();
});

it('redirects away from the notice once already verified', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/email/verify')
        ->assertRedirect('/');
});

it('verifies via a valid signed link', function (): void {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect('/');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('rejects an unsigned verification link', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get("/email/verify/{$user->id}/".sha1($user->email))
        ->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('resends the verification notification', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post('/email/verification-notification')
        ->assertRedirect();

    Notification::assertSentTo($user, VerifyEmail::class);
});
