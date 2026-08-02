<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Contracts\HasTranslatableFields;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * No reseller_id / TenantScoped: ownership lives on Course, inherited
 * transitively through quiz -> module|lesson -> course. `type` is a
 * plain string for now, not yet cast to an enum -- QuestionTypeEnum is
 * the next task's job, per CLAUDE.md §5's own split between the fixed
 * set of type names and the per-type behaviour classes.
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
            'settings' => 'array',
            'payload' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
