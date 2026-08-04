<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Quizzes;

/**
 * SubmitQuizAttempt itself takes a plain array, same "no DTO for an
 * existing entity's bulk data" reasoning as BulkAssignCourseData -- this
 * exists only so the FormRequest has something to return from toDto().
 */
final readonly class SubmitQuizAttemptData
{
    /**
     * @param  array<int, mixed>  $answersByQuestionId
     */
    public function __construct(
        public array $answersByQuestionId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $rawAnswers */
        $rawAnswers = $data['answers'] ?? [];

        $answersByQuestionId = [];

        foreach ($rawAnswers as $questionId => $answer) {
            $answersByQuestionId[(int) $questionId] = $answer;
        }

        return new self(answersByQuestionId: $answersByQuestionId);
    }
}
