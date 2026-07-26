<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Concerns\TenantScoped;
use Database\Factories\ResellerKlantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
}
