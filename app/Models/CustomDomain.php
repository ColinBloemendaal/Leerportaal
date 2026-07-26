<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Concerns\TenantScoped;
use App\Enums\CustomDomainStatus;
use Database\Factories\CustomDomainFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CustomDomain extends Model
{
    use HasAuditLog;

    /** @use HasFactory<CustomDomainFactory> */
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
            'status' => CustomDomainStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Reseller, $this>
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }
}
