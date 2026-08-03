<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ?User $user
 * @property ?Quiz $quiz
 * @property int $attempt_number
 * @property ?float $score
 * @property ?float $max_score
 * @property ?bool $passed
 * @property Carbon $started_at
 * @property ?Carbon $submitted_at
 */
final class QuizAttemptIndexResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cursist_name' => $this->user?->name,
            'quiz_id' => $this->quiz?->id,
            'quiz_type' => $this->quiz?->type->value,
            'attempt_number' => $this->attempt_number,
            'score' => $this->score,
            'max_score' => $this->max_score,
            'passed' => $this->passed,
            'started_at' => $this->started_at->toIso8601String(),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
        ];
    }
}
