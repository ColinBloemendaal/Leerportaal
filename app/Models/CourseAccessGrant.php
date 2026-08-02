<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Concerns\TenantScoped;
use App\Exceptions\InvalidCourseAccessGrantException;
use Database\Factories\CourseAccessGrantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Platform -> reseller grant of either one catalog course or a whole
 * category. Single-owner (always belongs to exactly one reseller: the
 * one being granted access), so uses TenantScoped normally.
 */
final class CourseAccessGrant extends Model
{
    use HasAuditLog;

    /** @use HasFactory<CourseAccessGrantFactory> */
    use HasFactory;

    use SoftDeletes;
    use TenantScoped;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'granted_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::saving(function (self $grant): void {
            if (($grant->course_id === null) === ($grant->course_category_id === null)) {
                throw InvalidCourseAccessGrantException::mustHaveExactlyOneTarget();
            }
        });
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
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<CourseCategory, $this>
     */
    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }
}
