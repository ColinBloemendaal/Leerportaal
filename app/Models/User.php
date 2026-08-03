<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Enums\DigestFrequency;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * One table, one guard. reseller_id is nullable: null means platform
 * staff. Platform-owned per CLAUDE.md §3 -- does not use TenantScoped,
 * since the table mixes platform and reseller-owned rows; reseller-scoped
 * user queries are explicit (repository methods), not a blanket scope.
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    use HasAuditLog;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * Mirrors the notification_digest_frequency column's DB default: a
     * freshly-instantiated (not-yet-inserted-or-refreshed) User must
     * already read as DigestFrequency::Immediate in memory, or code that
     * notifies a just-created user (e.g. AcceptInvite) before any ->fresh()
     * call would see a null cast and RespectsPreferences would wrongly
     * treat it as "digest, not immediate" and strip the mail channel.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'notification_digest_frequency' => 'immediate',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'platform_role' => Role::class,
            'notification_digest_frequency' => DigestFrequency::class,
            'notification_digest_sent_at' => 'immutable_datetime',
        ];
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    /**
     * Proxy for "platform roles" until real roles exist (Phase 1 task 34)
     * -- see CLAUDE.md §3, "platform users" is the closest settled concept.
     */
    public function requiresTwoFactorAuthentication(): bool
    {
        return $this->reseller_id === null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->platform_role === Role::SuperAdmin;
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
        // Explicit FK: Eloquent's default inference from the relation
        // name would guess reseller_klant_id, but the actual column
        // (matching the resellerklanten table's own spelling) has no
        // underscore between "reseller" and "klant".
        return $this->belongsTo(ResellerKlant::class, 'resellerklant_id');
    }

    /**
     * @return HasMany<QuizAttempt, $this>
     */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Courses assigned to this user as a cursist.
     *
     * @return HasMany<CourseAssignment, $this>
     */
    public function courseAssignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class);
    }

    /**
     * @return BelongsToMany<Group, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')->withTimestamps();
    }
}
