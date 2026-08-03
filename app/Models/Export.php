<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Enums\ExportStatus;
use App\Enums\FilterableResource;
use Database\Factories\ExportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * No TenantScoped: scoped by user_id for ownership (a user only ever
 * sees their own exports), same reasoning as SavedFilter. reseller_id
 * is carried for resource types whose repository needs an ambient
 * TenantContext when the queued job runs -- see the migration's comment.
 */
final class Export extends Model
{
    use HasAuditLog;

    /** @use HasFactory<ExportFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resource_type' => FilterableResource::class,
            'status' => ExportStatus::class,
            'filters' => 'array',
            'expires_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Reseller, $this>
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isDownloadable(): bool
    {
        return $this->status === ExportStatus::Completed && $this->path !== null && ! $this->isExpired();
    }
}
