<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Enums\QuizType;
use App\Exceptions\InvalidQuizParentException;
use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * No reseller_id / TenantScoped: ownership lives on Course, inherited
 * transitively through module -> course or lesson -> module -> course,
 * same reasoning as Module/Lesson/Block.
 */
final class Quiz extends Model
{
    use HasAuditLog;

    /** @use HasFactory<QuizFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => QuizType::class,
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $quiz): void {
            if (($quiz->module_id === null) === ($quiz->lesson_id === null)) {
                throw InvalidQuizParentException::mustHaveExactlyOneParent();
            }
        });
    }

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
