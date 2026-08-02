<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Contracts\HasTranslatableFields;
use App\Enums\QuestionTypeEnum;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * A reusable question-bank entry, not owned by any single quiz --
 * attachment to a quiz (and per-quiz ordering) lives entirely in the
 * quiz_questions pivot via quizzes(), so the same question can be
 * reused across many quizzes. Deliberately does NOT use TenantScoped,
 * same mixed-ownership reasoning as Course/CourseCategory/Media:
 * reseller_id null = a platform-shared bank question, set = one
 * reseller's own. Visibility is composed explicitly in
 * App\Repositories\Eloquent\EloquentQuestionRepository.
 */
final class Question extends Model implements HasTranslatableFields
{
    use HasAuditLog;

    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * @var list<string>
     */
    public array $translatable = ['prompt'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => QuestionTypeEnum::class,
            'settings' => 'array',
            'payload' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Reseller, $this>
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * @return BelongsToMany<Quiz, $this>
     */
    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'quiz_questions')
            ->withPivot('order')
            ->withTimestamps();
    }
}
