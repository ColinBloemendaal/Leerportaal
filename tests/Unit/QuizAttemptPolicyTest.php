<?php

declare(strict_types=1);

use App\Models\QuizAttempt;
use App\Models\User;
use App\Policies\QuizAttemptPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('allows only the attempt\'s own user to submit it', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $attempt = QuizAttempt::factory()->for($owner, 'user')->create();

    $policy = new QuizAttemptPolicy;

    expect($policy->submit($owner, $attempt))->toBeTrue()
        ->and($policy->submit($stranger, $attempt))->toBeFalse();
});
