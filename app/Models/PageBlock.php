<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use Database\Factories\PageBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * No reseller_id / TenantScoped: ownership lives on Page, inherited
 * transitively -- same reasoning as Block on Lesson.
 */
final class PageBlock extends Model
{
    use HasAuditLog;

    /** @use HasFactory<PageBlockFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'visible' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
