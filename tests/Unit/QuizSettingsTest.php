<?php

declare(strict_types=1);

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('defaults to a quiz settings object with every field null', function (): void {
    $quiz = Quiz::factory()->create();

    expect($quiz->fresh()->settings)->toBeInstanceOf(QuizSettingsData::class)
        ->and($quiz->fresh()->settings->timeLimitMinutes)->toBeNull()
        ->and($quiz->fresh()->settings->attemptLimit)->toBeNull()
        ->and($quiz->fresh()->settings->passThresholdPercent)->toBeNull()
        ->and($quiz->fresh()->settings->cooldownMinutesBetweenAttempts)->toBeNull()
        ->and($quiz->fresh()->settings->questionPoolSize)->toBeNull();
});

it('persists and reloads exam settings through the cast', function (): void {
    $settings = new QuizSettingsData(
        timeLimitMinutes: 30,
        attemptLimit: 3,
        passThresholdPercent: 70,
        cooldownMinutesBetweenAttempts: 60,
        questionPoolSize: 10,
    );

    $quiz = Quiz::factory()->exam()->withSettings($settings)->create();

    $reloaded = $quiz->fresh()->settings;

    expect($reloaded->timeLimitMinutes)->toBe(30)
        ->and($reloaded->attemptLimit)->toBe(3)
        ->and($reloaded->passThresholdPercent)->toBe(70)
        ->and($reloaded->cooldownMinutesBetweenAttempts)->toBe(60)
        ->and($reloaded->questionPoolSize)->toBe(10);
});

it('hydrates settings from a plain array via fromArray', function (): void {
    $settings = QuizSettingsData::fromArray([
        'time_limit_minutes' => 45,
        'attempt_limit' => 1,
        'pass_threshold_percent' => 80,
    ]);

    expect($settings->timeLimitMinutes)->toBe(45)
        ->and($settings->attemptLimit)->toBe(1)
        ->and($settings->passThresholdPercent)->toBe(80)
        ->and($settings->cooldownMinutesBetweenAttempts)->toBeNull()
        ->and($settings->questionPoolSize)->toBeNull();
});
