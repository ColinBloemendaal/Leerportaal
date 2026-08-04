<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Reseller;
use App\Models\User;
use App\Repositories\Eloquent\EloquentQuizAttemptRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('scopes attempts to the current reseller via the attempting user, filtered by passed', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $quiz = Quiz::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    QuizAttempt::factory()->for($quiz)->for($cursist, 'user')->create(['passed' => true, 'attempt_number' => 1]);
    QuizAttempt::factory()->for($quiz)->for($cursist, 'user')->create(['passed' => false, 'attempt_number' => 2]);

    $otherCursist = User::factory()->create(['reseller_id' => $otherReseller->id]);
    QuizAttempt::factory()->for($quiz)->for($otherCursist, 'user')->create(['passed' => true]);

    $repository = app(EloquentQuizAttemptRepository::class);
    $filters = new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: ['passed' => '1']);

    $result = $repository->paginate($filters);

    expect($result->total())->toBe(1);
});

it('finds every attempt for a user regardless of reseller', function (): void {
    $quiz = Quiz::factory()->create();
    $cursist = User::factory()->create();
    QuizAttempt::factory()->for($quiz)->for($cursist, 'user')->create(['attempt_number' => 1]);
    QuizAttempt::factory()->for($quiz)->for($cursist, 'user')->create(['attempt_number' => 2]);
    QuizAttempt::factory()->for($quiz)->create(); // someone else's

    $found = app(EloquentQuizAttemptRepository::class)->forUser($cursist->id);

    expect($found)->toHaveCount(2);
});

it('finds an attempt by id with no additional scoping', function (): void {
    $attempt = QuizAttempt::factory()->create();

    expect(app(EloquentQuizAttemptRepository::class)->findById($attempt->id)?->id)->toBe($attempt->id);
});

it('finds the most recent submitted attempt for a quiz and user, ignoring in-progress ones', function (): void {
    $quiz = Quiz::factory()->create();
    $cursist = User::factory()->create();
    QuizAttempt::factory()->for($quiz)->for($cursist, 'user')->submitted()->create(['attempt_number' => 1]);
    $latest = QuizAttempt::factory()->for($quiz)->for($cursist, 'user')->submitted()->create(['attempt_number' => 2]);
    QuizAttempt::factory()->for($quiz)->for($cursist, 'user')->create(['attempt_number' => 3]); // still in progress

    $found = app(EloquentQuizAttemptRepository::class)->latestSubmittedForQuizAndUser($quiz->id, $cursist->id);

    expect($found?->id)->toBe($latest->id);
});
