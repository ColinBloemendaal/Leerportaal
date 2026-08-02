<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('is a platform-shared bank question by default', function (): void {
    $question = Question::factory()->create();

    expect($question->reseller_id)->toBeNull();
});

it('can be attached to more than one quiz, reused across them', function (): void {
    $question = Question::factory()->create();
    $quizA = Quiz::factory()->create();
    $quizB = Quiz::factory()->forModule()->create();

    $quizA->questions()->attach($question->id, ['order' => 0]);
    $quizB->questions()->attach($question->id, ['order' => 0]);

    expect($question->quizzes()->count())->toBe(2);
});

it('casts type to the QuestionTypeEnum', function (): void {
    $question = Question::factory()->create(['type' => 'true_false']);

    expect($question->fresh()->type)->toBe(QuestionTypeEnum::TrueFalse);
});

it('stores prompt as translatable content', function (): void {
    $question = Question::factory()->create();
    $question->setTranslation('prompt', 'nl', 'Wat is 2 + 2?');
    $question->setTranslation('prompt', 'en', 'What is 2 + 2?');
    $question->save();

    expect($question->fresh()->getTranslation('prompt', 'nl'))->toBe('Wat is 2 + 2?')
        ->and($question->fresh()->getTranslation('prompt', 'en'))->toBe('What is 2 + 2?');
});

it('casts settings and payload to arrays', function (): void {
    $question = Question::factory()->create([
        'settings' => ['shuffle' => true],
        'payload' => ['options' => ['a', 'b', 'c'], 'correct' => 'b'],
    ]);

    expect($question->fresh()->settings)->toBe(['shuffle' => true])
        ->and($question->fresh()->payload)->toBe(['options' => ['a', 'b', 'c'], 'correct' => 'b']);
});
