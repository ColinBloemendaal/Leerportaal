<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Concerns\TenantScoped;
use Database\Factories\ResellerKlantCourseGrantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Reseller -> resellerklant grant of a single course. Single-owner
 * (always belongs to exactly one reseller: the one granting it), so
 * uses TenantScoped normally, same as CourseAccessGrant.
 */
final class ResellerKlantCourseGrant extends Model
{
    use HasAuditLog;

    /** @use HasFactory<ResellerKlantCourseGrantFactory> */
    use HasFactory;

    use SoftDeletes;
    use TenantScoped;

    // Explicit, same reasoning as ResellerKlant itself: the table follows
    // "resellerklant" (no underscore), not Eloquent's default snake_case
    // guess from the class name.
    protected $table = 'resellerklant_course_grants';

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

    /**
     * @return BelongsTo<Reseller, $this>
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * @return BelongsTo<ResellerKlant, $this>
     */
    public function resellerKlant(): BelongsTo
    {
        return $this->belongsTo(ResellerKlant::class, 'resellerklant_id');
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
