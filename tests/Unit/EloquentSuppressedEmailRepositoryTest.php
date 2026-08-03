<?php

declare(strict_types=1);

use App\Models\SuppressedEmail;
use App\Repositories\Eloquent\EloquentSuppressedEmailRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('reports an address as suppressed once a row exists for it', function (): void {
    SuppressedEmail::factory()->create(['email' => 'bounced@example.test']);

    $repository = new EloquentSuppressedEmailRepository;

    expect($repository->isSuppressed('bounced@example.test'))->toBeTrue()
        ->and($repository->isSuppressed('clean@example.test'))->toBeFalse();
});
