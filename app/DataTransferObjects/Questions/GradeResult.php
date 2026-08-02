<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Questions;

/**
 * The outcome of grading one answer to one question. Named exactly as
 * CLAUDE.md §5's QuestionType::grade() signature specifies, not
 * <Thing>Data, since it isn't optional here.
 */
final readonly class GradeResult
{
    private function __construct(
        public ?float $pointsAwarded,
        public float $pointsPossible,
        public bool $requiresManualGrading,
        public ?string $feedback = null,
    ) {}

    public static function correct(float $pointsPossible, ?string $feedback = null): self
    {
        return new self($pointsPossible, $pointsPossible, false, $feedback);
    }

    public static function partial(float $pointsAwarded, float $pointsPossible, ?string $feedback = null): self
    {
        return new self($pointsAwarded, $pointsPossible, false, $feedback);
    }

    public static function incorrect(float $pointsPossible, ?string $feedback = null): self
    {
        return new self(0.0, $pointsPossible, false, $feedback);
    }

    public static function pendingManualGrading(float $pointsPossible): self
    {
        return new self(null, $pointsPossible, true);
    }

    /**
     * Null when grading is still pending (manual grading types).
     */
    public function isCorrect(): ?bool
    {
        if ($this->requiresManualGrading) {
            return null;
        }

        return $this->pointsAwarded === $this->pointsPossible;
    }
}
