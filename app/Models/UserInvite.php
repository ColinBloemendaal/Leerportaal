<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Concerns\TenantScoped;
use App\Enums\Role;
use Database\Factories\UserInviteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class UserInvite extends Model
{
    use HasAuditLog;

    /** @use HasFactory<UserInviteFactory> */
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
            'role' => Role::class,
            'accepted_at' => 'datetime',
        ];
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null;
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
