<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Support\Facades\URL;

function signedAcceptUrl(Reseller $reseller, UserInvite $invite): string
{
    return URL::temporarySignedRoute('invite.accept', now()->addDays(7), [
        'reseller' => $reseller->slug,
        'invite' => $invite->id,
        'hash' => sha1($invite->email),
    ]);
}

it('shows the accept-invite page for a valid signed link', function (): void {
    $reseller = Reseller::factory()->create();
    $invite = UserInvite::factory()->create(['reseller_id' => $reseller->id, 'email' => 'jane@example.test']);

    $this->get(signedAcceptUrl($reseller, $invite))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/AcceptInvite')->where('email', 'jane@example.test'));
});

it('rejects a tampered signature', function (): void {
    $reseller = Reseller::factory()->create();
    $invite = UserInvite::factory()->create(['reseller_id' => $reseller->id]);

    $url = signedAcceptUrl($reseller, $invite).'&tampered=1';

    $this->get($url)->assertForbidden();
});

it('redirects to login for a wrong hash', function (): void {
    $reseller = Reseller::factory()->create();
    $invite = UserInvite::factory()->create(['reseller_id' => $reseller->id, 'email' => 'jane@example.test']);

    $url = URL::temporarySignedRoute('invite.accept', now()->addDays(7), [
        'reseller' => $reseller->slug,
        'invite' => $invite->id,
        'hash' => sha1('someone-else@example.test'),
    ]);

    $this->get($url)->assertRedirect('/login');
});

it('completes signup and logs the user in', function (): void {
    $reseller = Reseller::factory()->create();
    $invite = UserInvite::factory()->create([
        'reseller_id' => $reseller->id,
        'email' => 'jane@example.test',
        'name' => 'Jane Doe',
    ]);

    $url = signedAcceptUrl($reseller, $invite);

    $this->post($url, [
        'password' => 'correct-password',
        'password_confirmation' => 'correct-password',
    ])->assertRedirect('/');

    $user = User::query()->where('email', 'jane@example.test')->first();
    expect($user)->not->toBeNull();
    $this->assertAuthenticatedAs($user);
    expect($invite->fresh()->isPending())->toBeFalse();
});

it('rejects accepting an already-accepted invite', function (): void {
    $reseller = Reseller::factory()->create();
    $invite = UserInvite::factory()->accepted()->create([
        'reseller_id' => $reseller->id,
        'email' => 'jane@example.test',
    ]);

    $url = signedAcceptUrl($reseller, $invite);

    $this->post($url, [
        'password' => 'correct-password',
        'password_confirmation' => 'correct-password',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});
