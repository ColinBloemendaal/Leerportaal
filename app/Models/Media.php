<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Deliberately does NOT use TenantScoped: a row is either platform-owned
 * (reseller_id null, used inside catalog courses) or owned by one
 * reseller (their own media library) -- the same mixed-ownership shape
 * as Course/CourseCategory, which the strict, fails-closed TenantScope
 * doesn't support. Visibility is composed explicitly in
 * App\Repositories\Eloquent\EloquentMediaRepository.
 */
final class Media extends Model
{
    use HasAuditLog;

    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return BelongsTo<Reseller, $this>
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
