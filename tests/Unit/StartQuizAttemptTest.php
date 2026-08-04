<?php

declare(strict_types=1);

use App\Actions\Quizzes\StartQuizAttempt;
use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Exceptions\QuizAttemptNotAllowedException;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function attachOrderedQuestions(Quiz $quiz, int $count): void
{
    Question::factory()->count($count)->create(['points' => 2])->each(
        fn (Question $question, int $index) => $quiz->questions()->attach($question->id, ['order' => $index]),
    );
}

it('starts a fresh attempt and freezes the question set as empty answer rows', function (): void {
    $quiz = Quiz::factory()->exam()->create();
    attachOrderedQuestions($quiz, 3);
    $user = User::factory()->create();

    $attempt = app(StartQuizAttempt::class)($quiz, $user);

    expect($attempt->attempt_number)->toBe(1)
        ->and($attempt->started_at)->not->toBeNull()
        ->and($attempt->submitted_at)->toBeNull()
        ->and($attempt->answers)->toHaveCount(3);

    foreach ($attempt->answers as $answer) {
        expect($answer->answer)->toBeNull()
            ->and($answer->points_possible)->toBe(2.0);
    }
});

it('resumes an existing in-progress attempt instead of starting a new one', function (): void {
    $quiz = Quiz::factory()->exam()->create();
    attachOrderedQuestions($quiz, 2);
    $user = User::factory()->create();

    $first = app(StartQuizAttempt::class)($quiz, $user);
    $second = app(StartQuizAttempt::class)($quiz, $user);

    expect($second->id)->toBe($first->id)
        ->and(QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $user->id)->count())->toBe(1);
});

it('enforces the attempt limit once every previous attempt is submitted', function (): void {
    $quiz = Quiz::factory()->exam()->withSettings(new QuizSettingsData(attemptLimit: 1))->create();
    attachOrderedQuestions($quiz, 1);
    $user = User::factory()->create();

    QuizAttempt::factory()->for($quiz)->for($user, 'user')->submitted()->create(['attempt_number' => 1]);

    expect(fn () => app(StartQuizAttempt::class)($quiz, $user))
        ->toThrow(QuizAttemptNotAllowedException::class);
});

it('enforces the cooldown between attempts', function (): void {
    $quiz = Quiz::factory()->exam()->withSettings(new QuizSettingsData(cooldownMinutesBetweenAttempts: 60))->create();
    attachOrderedQuestions($quiz, 1);
    $user = User::factory()->create();

    QuizAttempt::factory()->for($quiz)->for($user, 'user')->submitted()->create([
        'attempt_number' => 1,
        'submitted_at' => now()->subMinutes(30),
    ]);

    expect(fn () => app(StartQuizAttempt::class)($quiz, $user))
        ->toThrow(QuizAttemptNotAllowedException::class);
});

it('allows a new attempt once the cooldown has elapsed', function (): void {
    $quiz = Quiz::factory()->exam()->withSettings(new QuizSettingsData(cooldownMinutesBetweenAttempts: 60))->create();
    attachOrderedQuestions($quiz, 1);
    $user = User::factory()->create();

    QuizAttempt::factory()->for($quiz)->for($user, 'user')->submitted()->create([
        'attempt_number' => 1,
        'submitted_at' => now()->subMinutes(90),
    ]);

    $attempt = app(StartQuizAttempt::class)($quiz, $user);

    expect($attempt->attempt_number)->toBe(2);
});
