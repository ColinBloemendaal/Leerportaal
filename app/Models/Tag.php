<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A shared, platform-wide taxonomy -- no reseller_id at all, unlike
 * CourseCategory/Course/Media. Tags are meant to be a common vocabulary
 * usable across every reseller's catalog rather than duplicated per
 * reseller, so there's no mixed-ownership visibility question here and
 * no tenancy:audit exemption needed.
 */
final class Tag extends Model
{
    use HasAuditLog;

    /** @use HasFactory<TagFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return BelongsToMany<Course, $this>
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)->withTimestamps();
    }
}
