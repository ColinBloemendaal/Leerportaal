<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ResellerStatus;
use Database\Factories\ResellerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Platform-owned -- does not use TenantScoped. See CLAUDE.md §2.
 */
final class Reseller extends Model
{
    /** @use HasFactory<ResellerFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ResellerStatus::class,
            'settings' => 'array',
        ];
    }
}
