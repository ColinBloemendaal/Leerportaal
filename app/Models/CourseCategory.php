<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use Database\Factories\CourseCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * Deliberately does NOT use TenantScoped: a row is either platform-owned
 * (reseller_id null, visible to every reseller) or owned by one reseller
 * (visible only to them) -- a shape the strict, fails-closed TenantScope
 * doesn't support. Visibility is composed explicitly in
 * App\Repositories\Eloquent\EloquentCourseCategoryRepository instead.
 */
final class CourseCategory extends Model
{
    use HasAuditLog;

    /** @use HasFactory<CourseCategoryFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * @var list<string>
     */
    public array $translatable = ['name'];

    /**
     * @return BelongsTo<Reseller, $this>
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * @return BelongsTo<CourseCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<CourseCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
