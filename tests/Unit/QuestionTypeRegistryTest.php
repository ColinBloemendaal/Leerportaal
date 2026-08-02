<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Questions\Contracts\QuestionType;
use App\Questions\QuestionTypeRegistry;
use App\Questions\Types\MultipleChoiceQuestion;
use Tests\TestCase;

uses(TestCase::class);

// All 14 types are now implemented, so the "not yet registered" path
// (App\Exceptions equivalent: OutOfBoundsException) that every prior
// type's commit exercised against the next-up case along the way no
// longer has a real unmapped case left to test against -- this asserts
// the completed, positive state instead: every case resolves.
it('resolves every case to a QuestionType implementation whose key matches', function (QuestionTypeEnum $type): void {
    $implementation = (new QuestionTypeRegistry)->resolve($type);

    expect($implementation)->toBeInstanceOf(QuestionType::class)
        ->and($implementation::key())->toBe($type);
})->with(QuestionTypeEnum::cases());

it('resolves multiple_choice to its implementation', function (): void {
    expect((new QuestionTypeRegistry)->resolve(QuestionTypeEnum::MultipleChoice))
        ->toBeInstanceOf(MultipleChoiceQuestion::class);
});

it('has a human label for every case', function (QuestionTypeEnum $type): void {
    expect($type->label())->not->toBe('');
})->with(QuestionTypeEnum::cases());
