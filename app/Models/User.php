<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $guarded = [];

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
        return $this->belongsTo(ResellerKlant::class);
    }
}
