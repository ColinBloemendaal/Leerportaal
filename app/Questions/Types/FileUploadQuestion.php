<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {"allowed_mime_types": [string, ...], "max_size_bytes": int|null}
 * `answer` (submitted): a reference to the uploaded file -- what exactly
 * that reference is (a Media id, a raw upload) is the quiz_attempts/
 * question_answers task's job to define, since that's what actually
 * persists a submission; this type only describes the upload
 * constraints and always defers grading to a human, same as essay.
 */
final class FileUploadQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::FileUpload;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::FileUpload->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'allowed_mime_types' => ['sometimes', 'array'],
            'allowed_mime_types.*' => ['required_with:allowed_mime_types', 'string'],
            'max_size_bytes' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/FileUploadQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/FileUploadQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        return GradeResult::pendingManualGrading((float) $question->points);
    }

    public function isAutoGradable(): bool
    {
        return false;
    }
}
