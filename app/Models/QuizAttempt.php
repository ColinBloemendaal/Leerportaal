<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use Database\Factories\QuizAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * No reseller_id / TenantScoped: same "ownership lives elsewhere,
 * inherited transitively" reasoning as Quiz/Question -- visibility for
 * an attempt follows the attempting User's own reseller, not a column
 * on this table.
 */
final class QuizAttempt extends Model
{
    use HasAuditLog;

    /** @use HasFactory<QuizAttemptFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'submitted_at' => 'immutable_datetime',
            'passed' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<QuestionAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class);
    }
}
