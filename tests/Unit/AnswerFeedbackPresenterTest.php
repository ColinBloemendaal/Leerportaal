<?php

declare(strict_types=1);

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Enums\FeedbackVisibility;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\Grading\AnswerFeedbackPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function feedbackPresenter(): AnswerFeedbackPresenter
{
    return new AnswerFeedbackPresenter;
}

it('shows feedback immediately when configured to', function (): void {
    $quiz = Quiz::factory()->withSettings(new QuizSettingsData(feedbackVisibility: FeedbackVisibility::Immediate))->create();
    $attempt = QuizAttempt::factory()->for($quiz)->create(['submitted_at' => null]);
    $answer = QuestionAnswer::factory()->for($attempt, 'quizAttempt')->for(Question::factory())
        ->create(['feedback' => 'Nicely done.']);

    expect(feedbackPresenter()->feedbackFor($attempt, $answer))->toBe('Nicely done.');
});

it('hides feedback until submission when configured that way', function (): void {
    $quiz = Quiz::factory()->withSettings(new QuizSettingsData(feedbackVisibility: FeedbackVisibility::AfterSubmission))->create();
    $unsubmitted = QuizAttempt::factory()->for($quiz)->create(['submitted_at' => null]);
    $submitted = QuizAttempt::factory()->for($quiz)->submitted()->create();

    $unsubmittedAnswer = QuestionAnswer::factory()->for($unsubmitted, 'quizAttempt')->for(Question::factory())
        ->create(['feedback' => 'Feedback text']);
    $submittedAnswer = QuestionAnswer::factory()->for($submitted, 'quizAttempt')->for(Question::factory())
        ->create(['feedback' => 'Feedback text']);

    expect(feedbackPresenter()->feedbackFor($unsubmitted, $unsubmittedAnswer))->toBeNull()
        ->and(feedbackPresenter()->feedbackFor($submitted, $submittedAnswer))->toBe('Feedback text');
});

it('never shows feedback when configured to never', function (): void {
    $quiz = Quiz::factory()->withSettings(new QuizSettingsData(feedbackVisibility: FeedbackVisibility::Never))->create();
    $attempt = QuizAttempt::factory()->for($quiz)->submitted()->create();
    $answer = QuestionAnswer::factory()->for($attempt, 'quizAttempt')->for(Question::factory())
        ->create(['feedback' => 'Hidden forever']);

    expect(feedbackPresenter()->feedbackFor($attempt, $answer))->toBeNull();
});
