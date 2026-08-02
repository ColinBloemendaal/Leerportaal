<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use Database\Factories\QuestionAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * No reseller_id / TenantScoped: ownership follows the parent
 * QuizAttempt's, same as the rest of the quiz/question hierarchy.
 */
final class QuestionAnswer extends Model
{
    use HasAuditLog;

    /** @use HasFactory<QuestionAnswerFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'answer' => 'array',
            'is_correct' => 'boolean',
            'requires_manual_grading' => 'boolean',
            'graded_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<QuizAttempt, $this>
     */
    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
