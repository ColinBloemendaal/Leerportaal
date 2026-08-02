<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Concerns\HasAuditLog;
use App\Concerns\TenantScoped;
use App\Enums\AssignmentBillingState;
use Database\Factories\CourseAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The billable event per CLAUDE.md §11 -- assigning one course to one
 * cursist. Single-owner (always belongs to exactly one reseller, unlike
 * Course/Question/Media's mixed platform/reseller ownership), so this
 * uses TenantScoped normally rather than a repository visibility method.
 */
final class CourseAssignment extends Model
{
    use HasAuditLog;

    /** @use HasFactory<CourseAssignmentFactory> */
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
            'assigned_at' => 'immutable_datetime',
            'first_opened_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'deadline_at' => 'immutable_datetime',
            'reminder_days_before' => 'array',
            'billing_state' => AssignmentBillingState::class,
            'price_cents' => MoneyCast::class,
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
     * The cursist this course is assigned to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    /**
     * @return HasMany<BlockProgress, $this>
     */
    public function blockProgress(): HasMany
    {
        return $this->hasMany(BlockProgress::class);
    }

    /**
     * @return HasOne<Certificate, $this>
     */
    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }
}
