<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Concerns\TenantScoped;
use Database\Factories\ResellerKlantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ResellerKlant extends Model
{
    use HasAuditLog;

    /** @use HasFactory<ResellerKlantFactory> */
    use HasFactory;

    use SoftDeletes;
    use TenantScoped;

    protected $table = 'resellerklanten';

    protected $guarded = [];

    /**
     * @return BelongsTo<Reseller, $this>
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * @return HasMany<Group, $this>
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Users belonging to this klant -- cursisten, not the reseller's own
     * staff (who have no resellerklant_id).
     *
     * @return HasMany<User, $this>
     */
    public function cursisten(): HasMany
    {
        return $this->hasMany(User::class, 'resellerklant_id');
    }
}
