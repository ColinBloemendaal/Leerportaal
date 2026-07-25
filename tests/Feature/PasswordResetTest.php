<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('shows the forgot-password page', function (): void {
    $this->get('/forgot-password')->assertOk();
});

it('sends a reset link for a known email', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('responds the same way for an unknown email, to avoid leaking which addresses exist', function (): void {
    Notification::fake();

    $this->post('/forgot-password', ['email' => 'nobody@example.test'])
        ->assertSessionHas('status');

    Notification::assertNothingSent();
});

it('resets the password with a valid token', function (): void {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect('/login');

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

it('rejects an invalid token', function (): void {
    $user = User::factory()->create();

    $this->post('/reset-password', [
        'token' => 'not-a-real-token',
        'email' => $user->email,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertSessionHasErrors('email');
});
