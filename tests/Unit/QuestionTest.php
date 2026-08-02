<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a quiz', function (): void {
    $quiz = Quiz::factory()->create();
    $question = Question::factory()->for($quiz)->create();

    expect($question->quiz->is($quiz))->toBeTrue();
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
