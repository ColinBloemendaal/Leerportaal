<?php

declare(strict_types=1);

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Questions\QuestionTypeRegistry;
use App\Services\Grading\GradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function gradingService(): GradingService
{
    return new GradingService(new QuestionTypeRegistry);
}

it('auto-grades a multiple_choice answer', function (): void {
    $question = Question::factory()->create([
        'type' => 'multiple_choice',
        'points' => 4,
        'payload' => [
            'options' => [['id' => 'a', 'text' => 'Paris'], ['id' => 'b', 'text' => 'Berlin']],
            'correct_option_id' => 'a',
        ],
    ]);
    $answer = QuestionAnswer::factory()->for($question)->create(['answer' => 'a', 'points_possible' => 4]);

    $graded = gradingService()->gradeAnswer($answer);

    expect($graded->is_correct)->toBeTrue()
        ->and($graded->points_awarded)->toBe(4.0)
        ->and($graded->requires_manual_grading)->toBeFalse()
        ->and($graded->graded_at)->not->toBeNull();
});

it('re-grading an auto-graded answer recomputes it (regrade capability)', function (): void {
    $question = Question::factory()->create([
        'type' => 'multiple_choice',
        'points' => 4,
        'payload' => [
            'options' => [['id' => 'a', 'text' => 'Paris'], ['id' => 'b', 'text' => 'Berlin']],
            'correct_option_id' => 'a',
        ],
    ]);
    $answer = QuestionAnswer::factory()->for($question)->create(['answer' => 'a', 'points_possible' => 4]);
    gradingService()->gradeAnswer($answer);

    // The correct option was wrong in the bank question -- fixed after
    // the fact, then the same answer is regraded.
    $question->update(['payload' => [
        'options' => [['id' => 'a', 'text' => 'Paris'], ['id' => 'b', 'text' => 'Berlin']],
        'correct_option_id' => 'b',
    ]]);

    $regraded = gradingService()->gradeAnswer($answer->fresh());

    expect($regraded->is_correct)->toBeFalse();
});

it('marks a manual-grading answer pending on first grade, without a score', function (): void {
    $question = Question::factory()->create(['type' => 'essay', 'points' => 10]);
    $answer = QuestionAnswer::factory()->for($question)->create(['answer' => 'My essay.', 'points_possible' => 10]);

    $graded = gradingService()->gradeAnswer($answer);

    expect($graded->requires_manual_grading)->toBeTrue()
        ->and($graded->points_awarded)->toBeNull()
        ->and($graded->graded_at)->toBeNull();
});

it('does not re-run type grading on an answer already classified as manual', function (): void {
    $question = Question::factory()->create(['type' => 'essay', 'points' => 10]);
    $answer = QuestionAnswer::factory()->for($question)->create(['answer' => 'My essay.', 'points_possible' => 10]);
    gradingService()->gradeAnswer($answer);

    // Simulate a human already having scored it.
    gradingService()->gradeManually($answer->fresh(), 7.0, 'Solid but missed one point.');

    // A later (accidental or scheduled) gradeAnswer() call must not wipe
    // out the human's score.
    $result = gradingService()->gradeAnswer($answer->fresh());

    expect($result->points_awarded)->toBe(7.0)
        ->and($result->feedback)->toBe('Solid but missed one point.');
});

it('manually grades an answer as correct when the full points are awarded', function (): void {
    $question = Question::factory()->create(['type' => 'essay', 'points' => 10]);
    $answer = QuestionAnswer::factory()->for($question)->create(['answer' => 'My essay.', 'points_possible' => 10]);

    $graded = gradingService()->gradeManually($answer, 10.0, 'Excellent.');

    expect($graded->is_correct)->toBeTrue()
        ->and($graded->points_awarded)->toBe(10.0)
        ->and($graded->graded_at)->not->toBeNull();
});

it('aggregates an attempt score across auto-graded answers and marks it passed', function (): void {
    $quiz = Quiz::factory()->withSettings(new QuizSettingsData(passThresholdPercent: 50))->create();
    $attempt = QuizAttempt::factory()->for($quiz)->create();

    $correctQuestion = Question::factory()->create([
        'type' => 'true_false', 'points' => 5, 'payload' => ['correct_answer' => true],
    ]);
    $incorrectQuestion = Question::factory()->create([
        'type' => 'true_false', 'points' => 5, 'payload' => ['correct_answer' => true],
    ]);

    QuestionAnswer::factory()->for($attempt, 'quizAttempt')->for($correctQuestion)
        ->create(['answer' => true, 'points_possible' => 5]);
    QuestionAnswer::factory()->for($attempt, 'quizAttempt')->for($incorrectQuestion)
        ->create(['answer' => false, 'points_possible' => 5]);

    $graded = gradingService()->gradeAttempt($attempt);

    expect($graded->score)->toBe(5.0)
        ->and($graded->max_score)->toBe(10.0)
        ->and($graded->passed)->toBeTrue();
});

it('marks an attempt as not yet decided while a manual answer is still pending', function (): void {
    $quiz = Quiz::factory()->withSettings(new QuizSettingsData(passThresholdPercent: 50))->create();
    $attempt = QuizAttempt::factory()->for($quiz)->create();
    $essayQuestion = Question::factory()->create(['type' => 'essay', 'points' => 10]);

    QuestionAnswer::factory()->for($attempt, 'quizAttempt')->for($essayQuestion)
        ->create(['answer' => 'My essay.', 'points_possible' => 10]);

    $graded = gradingService()->gradeAttempt($attempt);

    expect($graded->passed)->toBeNull();
});

it('leaves passed null for a practice quiz with no pass threshold', function (): void {
    $quiz = Quiz::factory()->create();
    $attempt = QuizAttempt::factory()->for($quiz)->create();
    $question = Question::factory()->create(['type' => 'true_false', 'points' => 5, 'payload' => ['correct_answer' => true]]);

    QuestionAnswer::factory()->for($attempt, 'quizAttempt')->for($question)
        ->create(['answer' => true, 'points_possible' => 5]);

    $graded = gradingService()->gradeAttempt($attempt);

    expect($graded->passed)->toBeNull();
});
