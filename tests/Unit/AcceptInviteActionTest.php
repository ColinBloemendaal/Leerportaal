<?php

declare(strict_types=1);

use App\Actions\Auth\AcceptInvite;
use App\DataTransferObjects\Auth\AcceptInviteData;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Models\UserInvite;
use App\Repositories\Eloquent\EloquentUserInviteRepository;
use App\Tenancy\TenantContext;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($this->reseller);
    $this->action = new AcceptInvite(new EloquentUserInviteRepository, app(ConnectionInterface::class));
});

it('creates the user and marks the invite accepted', function (): void {
    $klant = ResellerKlant::factory()->for($this->reseller, 'reseller')->create();
    $invite = UserInvite::factory()->create([
        'reseller_id' => $this->reseller->id,
        'resellerklant_id' => $klant->id,
        'email' => 'jane@example.test',
        'name' => 'Jane Doe',
    ]);

    $user = ($this->action)(new AcceptInviteData(
        inviteId: $invite->id,
        hash: sha1('jane@example.test'),
        password: 'correct-password',
    ));

    expect($user->email)->toBe('jane@example.test')
        ->and($user->reseller_id)->toBe($this->reseller->id)
        ->and($user->resellerklant_id)->toBe($klant->id)
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($invite->fresh()->isPending())->toBeFalse();
});

it('rejects a mismatched hash', function (): void {
    $invite = UserInvite::factory()->create(['reseller_id' => $this->reseller->id, 'email' => 'jane@example.test']);

    $data = new AcceptInviteData(inviteId: $invite->id, hash: 'wrong-hash', password: 'correct-password');

    expect(fn () => ($this->action)($data))->toThrow(ValidationException::class);
});

it('rejects an already-accepted invite', function (): void {
    $invite = UserInvite::factory()->accepted()->create([
        'reseller_id' => $this->reseller->id,
        'email' => 'jane@example.test',
    ]);

    $data = new AcceptInviteData(inviteId: $invite->id, hash: sha1('jane@example.test'), password: 'correct-password');

    expect(fn () => ($this->action)($data))->toThrow(ValidationException::class);
});

it('rejects when the email already has an account', function (): void {
    User::factory()->create(['email' => 'jane@example.test']);
    $invite = UserInvite::factory()->create(['reseller_id' => $this->reseller->id, 'email' => 'jane@example.test']);

    $data = new AcceptInviteData(inviteId: $invite->id, hash: sha1('jane@example.test'), password: 'correct-password');

    expect(fn () => ($this->action)($data))->toThrow(ValidationException::class);
});
