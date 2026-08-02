<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * No reseller_id / TenantScoped: ownership lives on Course, this
 * inherits visibility transitively through module -> course.
 */
final class Lesson extends Model
{
    use HasAuditLog;

    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * @return HasMany<Block, $this>
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
