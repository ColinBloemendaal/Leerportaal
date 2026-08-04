<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Concerns\TenantScoped;
use App\Enums\PageStatus;
use App\Enums\PageTemplate;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Single-owner (always belongs to exactly one reseller -- CLAUDE.md §1's
 * whitelabel pages are never platform-shared), so this uses TenantScoped
 * normally, same as Group/CourseAssignment.
 */
final class Page extends Model
{
    use HasAuditLog;

    /** @use HasFactory<PageFactory> */
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
            'template' => PageTemplate::class,
            'status' => PageStatus::class,
            'published_at' => 'immutable_datetime',
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
     * @return HasMany<PageBlock, $this>
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('order');
    }

    public function isPublished(): bool
    {
        return $this->status === PageStatus::Published;
    }
}
