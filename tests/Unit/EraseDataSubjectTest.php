<?php

declare(strict_types=1);

use App\Actions\Gdpr\EraseDataSubject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('scrubs the user\'s personal data and soft-deletes the account', function (): void {
    $user = User::factory()->create(['name' => 'Jane Cursist', 'email' => 'jane@example.test', 'remember_token' => 'original-token']);

    $erased = app(EraseDataSubject::class)($user);

    expect($erased->name)->toBe('Erased user')
        ->and($erased->email)->toBe("erased-user-{$user->id}@erased.invalid")
        ->and($erased->erased_at)->not->toBeNull()
        ->and($erased->two_factor_secret)->toBeNull()
        ->and($erased->two_factor_recovery_codes)->toBeNull()
        ->and($erased->two_factor_confirmed_at)->toBeNull()
        ->and($erased->remember_token)->not->toBe('original-token')
        ->and($erased->trashed())->toBeTrue();
});

it('deletes every active session for the erased user', function (): void {
    $user = User::factory()->create();

    DB::table('sessions')->insert([
        'id' => 'session-1',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test',
        'payload' => 'x',
        'last_activity' => time(),
    ]);

    app(EraseDataSubject::class)($user);

    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
});

it('is idempotent against an already-erased user', function (): void {
    $user = User::factory()->create();

    $erase = app(EraseDataSubject::class);
    $first = $erase($user);
    $erasedAt = $first->erased_at;

    $second = $erase($first->fresh());

    expect($second->erased_at)->not->toBeNull()
        ->and($second->erased_at->equalTo($erasedAt))->toBeTrue();
});
