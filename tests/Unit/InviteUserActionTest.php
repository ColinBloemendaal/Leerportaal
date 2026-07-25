<?php

declare(strict_types=1);

use App\Actions\Invites\InviteUser;
use App\DataTransferObjects\Invites\InviteUserData;
use App\Enums\Role;
use App\Mail\UserInvited;
use App\Models\Reseller;
use App\Models\User;
use App\Repositories\Eloquent\EloquentUserInviteRepository;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
    $this->reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($this->reseller);
    $this->action = new InviteUser(new EloquentUserInviteRepository, app(Mailer::class));
});

it('creates a pending invite and mails it', function (): void {
    $inviter = User::factory()->create(['reseller_id' => $this->reseller->id]);

    $invite = ($this->action)(InviteUserData::fromArray([
        'name' => 'Jane Doe',
        'email' => 'jane@example.test',
        'role' => Role::ResellerAdmin,
        'resellerklant_id' => null,
        'invited_by_user_id' => $inviter->id,
    ]));

    expect($invite->reseller_id)->toBe($this->reseller->id)
        ->and($invite->isPending())->toBeTrue();

    Mail::assertQueued(UserInvited::class, fn (UserInvited $mail): bool => $mail->invite->is($invite));
});

it('rejects an email that already has an account', function (): void {
    $inviter = User::factory()->create(['reseller_id' => $this->reseller->id]);
    $existing = User::factory()->create(['reseller_id' => $this->reseller->id]);

    $data = InviteUserData::fromArray([
        'name' => 'Jane Doe',
        'email' => $existing->email,
        'role' => Role::Cursist,
        'resellerklant_id' => null,
        'invited_by_user_id' => $inviter->id,
    ]);

    expect(fn () => ($this->action)($data))->toThrow(ValidationException::class);

    Mail::assertNothingSent();
});

it('rejects a duplicate pending invite for the same email', function (): void {
    $inviter = User::factory()->create(['reseller_id' => $this->reseller->id]);

    $data = InviteUserData::fromArray([
        'name' => 'Jane Doe',
        'email' => 'jane@example.test',
        'role' => Role::Cursist,
        'resellerklant_id' => null,
        'invited_by_user_id' => $inviter->id,
    ]);

    ($this->action)($data);

    expect(fn () => ($this->action)($data))->toThrow(ValidationException::class);
});
