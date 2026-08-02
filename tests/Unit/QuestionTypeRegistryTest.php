<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Questions\QuestionTypeRegistry;
use Tests\TestCase;

uses(TestCase::class);

it('throws for a type that has no registered implementation yet', function (): void {
    expect(fn () => (new QuestionTypeRegistry)->resolve(QuestionTypeEnum::MultipleChoice))
        ->toThrow(OutOfBoundsException::class);
});

it('has a human label for every case', function (QuestionTypeEnum $type): void {
    expect($type->label())->not->toBe('');
})->with(QuestionTypeEnum::cases());
