<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasAuditLog;
use App\Enums\SuppressionReason;
use Database\Factories\SuppressedEmailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Platform-wide, not TenantScoped: a hard bounce or spam complaint is a
 * property of the recipient mailbox itself, not of whichever reseller's
 * course happened to email it -- see the migration's own docblock.
 */
final class SuppressedEmail extends Model
{
    use HasAuditLog;

    /** @use HasFactory<SuppressedEmailFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reason' => SuppressionReason::class,
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
