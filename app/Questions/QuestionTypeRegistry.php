<?php

declare(strict_types=1);

namespace App\Questions;

use App\Enums\QuestionTypeEnum;
use App\Questions\Contracts\QuestionType;
use App\Questions\Types\MultipleChoiceQuestion;
use App\Questions\Types\DropdownInTextQuestion;
use App\Questions\Types\EssayQuestion;
use App\Questions\Types\FillInBlankQuestion;
use App\Questions\Types\HotspotImageQuestion;
use App\Questions\Types\MatchingQuestion;
use App\Questions\Types\MultipleResponseQuestion;
use App\Questions\Types\NumericQuestion;
use App\Questions\Types\OpenShortQuestion;
use App\Questions\Types\OrderingQuestion;
use App\Questions\Types\TrueFalseQuestion;
use OutOfBoundsException;

/**
 * Adding a question type = one QuestionTypeEnum case (already complete
 * -- all 14 exist) + one entry here + one class implementing
 * QuestionType + two Vue components. Mirrors App\Blocks\BlockTypeRegistry.
 *
 * Unlike blocks (all 7 types built in one commit), each question type is
 * its own separate TODO.md task/commit, so MAP starts empty and gains
 * one entry per type as that type's task lands -- an empty/partial map
 * is a valid, expected intermediate state here, not a bug.
 */
final class QuestionTypeRegistry
{
    /**
     * @var array<string, class-string<QuestionType>>
     */
    private const MAP = [
        QuestionTypeEnum::MultipleChoice->value => MultipleChoiceQuestion::class,
        QuestionTypeEnum::MultipleResponse->value => MultipleResponseQuestion::class,
        QuestionTypeEnum::TrueFalse->value => TrueFalseQuestion::class,
        QuestionTypeEnum::OpenShort->value => OpenShortQuestion::class,
        QuestionTypeEnum::Essay->value => EssayQuestion::class,
        QuestionTypeEnum::Matching->value => MatchingQuestion::class,
        QuestionTypeEnum::Ordering->value => OrderingQuestion::class,
        QuestionTypeEnum::FillInBlank->value => FillInBlankQuestion::class,
        QuestionTypeEnum::DropdownInText->value => DropdownInTextQuestion::class,
        QuestionTypeEnum::Numeric->value => NumericQuestion::class,
        QuestionTypeEnum::HotspotImage->value => HotspotImageQuestion::class,
    ];

    public function resolve(QuestionTypeEnum $type): QuestionType
    {
        if (! isset(self::MAP[$type->value])) {
            throw new OutOfBoundsException("No QuestionType implementation registered for [{$type->value}] yet.");
        }

        return app(self::MAP[$type->value]);
    }
}
