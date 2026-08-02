<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\CourseCompletionRuleCast;
use App\Casts\MoneyCast;
use App\Concerns\HasAuditLog;
use App\Contracts\HasTranslatableFields;
use App\Enums\CourseStatus;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * Deliberately does NOT use TenantScoped -- same reasoning as
 * CourseCategory: a row is either platform-owned (reseller_id null,
 * visible to every reseller) or owned by one reseller, a shape the
 * strict, fails-closed TenantScope doesn't support. Visibility is
 * composed explicitly in App\Repositories\Eloquent\EloquentCourseRepository.
 */
final class Course extends Model implements HasTranslatableFields
{
    use HasAuditLog;

    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * @var list<string>
     */
    public array $translatable = ['title', 'description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'available_locales' => 'array',
            'learning_objectives' => 'array',
            'published_at' => 'immutable_datetime',
            'reseller_set_price_cents' => MoneyCast::class,
            'platform_price_cents' => MoneyCast::class,
            'price_floor_override_cents' => MoneyCast::class,
            'completion_rule' => CourseCompletionRuleCast::class,
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
     * @return BelongsTo<Course, $this>
     */
    public function repeatsFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'repeats_from_course_id');
    }

    /**
     * @return HasMany<Course, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'repeats_from_course_id');
    }

    /**
     * @return HasMany<Module, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    /**
     * Courses that must be completed before this one.
     *
     * @return BelongsToMany<Course, $this>
     */
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'course_prerequisites',
            'course_id',
            'prerequisite_course_id',
        )->withTimestamps();
    }

    /**
     * Courses that list this one as a prerequisite.
     *
     * @return BelongsToMany<Course, $this>
     */
    public function requiredFor(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'course_prerequisites',
            'prerequisite_course_id',
            'course_id',
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * @return HasMany<CourseAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class);
    }
}
