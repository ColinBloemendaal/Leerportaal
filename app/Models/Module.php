<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * No reseller_id / TenantScoped: ownership lives on Course, this
 * inherits visibility transitively through course_id.
 */
final class Module extends Model
{
    use HasAuditLog;

    /** @use HasFactory<ModuleFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
