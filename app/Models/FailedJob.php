<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FailedJobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Maps Laravel's own failed_jobs table (see the framework's default
 * 0001_01_01_000002_create_jobs_table migration -- there is no separate
 * migration for it in this codebase). Read-only in practice: rows are
 * written by the queue worker on job failure, never by application
 * code, and retried/deleted via `php artisan queue:retry`/`:forget`.
 * Platform-wide by nature (a queue has no reseller_id), so it's outside
 * TenancyAuditCommand's scope entirely -- this table has no such column
 * to flag.
 */
final class FailedJob extends Model
{
    /** @use HasFactory<FailedJobFactory> */
    use HasFactory;

    protected $table = 'failed_jobs';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'failed_at' => 'immutable_datetime',
        ];
    }
}
