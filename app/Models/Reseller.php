<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Enums\ResellerStatus;
use App\Events\Permissions\ResellerCreated;
use Database\Factories\ResellerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Platform-owned -- does not use TenantScoped. See CLAUDE.md §2.
 */
final class Reseller extends Model
{
    use HasAuditLog;

    /** @use HasFactory<ResellerFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => ResellerCreated::class,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ResellerStatus::class,
            'settings' => 'array',
            'authoring_addon_expires_at' => 'immutable_datetime',
            'dpa_accepted_at' => 'immutable_datetime',
        ];
    }

    /**
     * CLAUDE.md §11: the authoring add-on unlocks custom course creation.
     * Consumed by CoursePolicy::create() -- kept here as a plain derived
     * boolean, same convention as User::isSuperAdmin()/hasEnabledTwoFactorAuthentication(),
     * not a Service: it's a one-column comparison, not an orchestration.
     */
    public function hasActiveAuthoringAddon(): bool
    {
        return $this->authoring_addon_expires_at !== null && $this->authoring_addon_expires_at->isFuture();
    }

    /**
     * CLAUDE.md §8 (GDPR): compares against config('gdpr.dpa_version'),
     * not just "has this reseller ever accepted a DPA" -- a version bump
     * (the document text changed) makes every reseller's prior
     * acceptance stale again.
     */
    public function hasAcceptedCurrentDpa(): bool
    {
        return $this->dpa_accepted_version === config('gdpr.dpa_version');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function dpaAcceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dpa_accepted_by_user_id');
    }
}
