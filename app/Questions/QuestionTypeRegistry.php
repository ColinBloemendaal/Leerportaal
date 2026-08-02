<?php

declare(strict_types=1);

namespace App\Questions;

use App\Enums\QuestionTypeEnum;
use App\Questions\Contracts\QuestionType;
use App\Questions\Types\DragDropImageQuestion;
use App\Questions\Types\DropdownInTextQuestion;
use App\Questions\Types\EssayQuestion;
use App\Questions\Types\FileUploadQuestion;
use App\Questions\Types\FillInBlankQuestion;
use App\Questions\Types\HotspotImageQuestion;
use App\Questions\Types\LikertQuestion;
use App\Questions\Types\MatchingQuestion;
use App\Questions\Types\MultipleChoiceQuestion;
use App\Questions\Types\MultipleResponseQuestion;
use App\Questions\Types\NumericQuestion;
use App\Questions\Types\OpenShortQuestion;
use App\Questions\Types\OrderingQuestion;
use App\Questions\Types\TrueFalseQuestion;

/**
 * Adding a question type = one QuestionTypeEnum case + one entry here +
 * one class implementing QuestionType + two Vue components. Mirrors
 * App\Blocks\BlockTypeRegistry -- MAP covers every QuestionTypeEnum
 * case (all 14), so resolve() indexes it directly rather than guarding
 * with isset()/an exception the way the map did while it was still
 * being filled in commit by commit.
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
        QuestionTypeEnum::DragDropImage->value => DragDropImageQuestion::class,
        QuestionTypeEnum::Likert->value => LikertQuestion::class,
        QuestionTypeEnum::FileUpload->value => FileUploadQuestion::class,
    ];

    public function resolve(QuestionTypeEnum $type): QuestionType
    {
        return app(self::MAP[$type->value]);
    }
}
