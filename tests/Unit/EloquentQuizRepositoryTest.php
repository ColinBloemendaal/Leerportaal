<?php

declare(strict_types=1);

use App\Models\Quiz;
use App\Repositories\Eloquent\EloquentQuizRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('finds a quiz by id', function (): void {
    $quiz = Quiz::factory()->create();

    expect(app(EloquentQuizRepository::class)->findById($quiz->id)?->id)->toBe($quiz->id);
});

it('returns null for a non-existent quiz id', function (): void {
    expect(app(EloquentQuizRepository::class)->findById(999999))->toBeNull();
});
