<?php

declare(strict_types=1);

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\Quizzes\QuizQuestionRandomizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function attachQuestions(Quiz $quiz, int $count): void
{
    Question::factory()->count($count)->create()->each(
        fn (Question $question, int $index) => $quiz->questions()->attach($question->id, ['order' => $index]),
    );
}

it('returns every attached question when there is no pool size limit', function (): void {
    $quiz = Quiz::factory()->create();
    attachQuestions($quiz, 5);

    $questions = (new QuizQuestionRandomizer)->questionsFor($quiz);

    expect($questions)->toHaveCount(5);
});

it('limits the returned questions to the configured pool size', function (): void {
    $quiz = Quiz::factory()->withSettings(new QuizSettingsData(questionPoolSize: 3))->create();
    attachQuestions($quiz, 10);

    $questions = (new QuizQuestionRandomizer)->questionsFor($quiz);

    expect($questions)->toHaveCount(3);

    $attachedIds = $quiz->questions()->pluck('questions.id');
    foreach ($questions as $question) {
        expect($attachedIds)->toContain($question->id);
    }
});

it('does not sample beyond the number of attached questions', function (): void {
    $quiz = Quiz::factory()->withSettings(new QuizSettingsData(questionPoolSize: 100))->create();
    attachQuestions($quiz, 4);

    $questions = (new QuizQuestionRandomizer)->questionsFor($quiz);

    expect($questions)->toHaveCount(4);
});

it('returns an empty collection for a quiz with no attached questions', function (): void {
    $quiz = Quiz::factory()->withSettings(new QuizSettingsData(questionPoolSize: 5))->create();

    $questions = (new QuizQuestionRandomizer)->questionsFor($quiz);

    expect($questions)->toHaveCount(0);
});

it('keeps pivot order when shuffleQuestions is false', function (): void {
    $quiz = Quiz::factory()->create();
    attachQuestions($quiz, 5);

    $expectedOrder = $quiz->questions()->pluck('questions.id')->all();

    $questions = (new QuizQuestionRandomizer)->questionsFor($quiz);

    expect($questions->pluck('id')->all())->toBe($expectedOrder);
});
