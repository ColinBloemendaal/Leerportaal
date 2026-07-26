<?php

declare(strict_types=1);

namespace App\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Applied to every model touching user data, course content,
 * assignments, billing, and permissions -- see CLAUDE.md §7. Logs every
 * attribute not explicitly excluded (models use $guarded = [], not
 * $fillable, so logUnguarded() is what actually picks anything up).
 */
trait HasAuditLog
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->logExcept($this->auditLogExcept());
    }

    /**
     * Override per model to add columns that must never be written to the
     * audit log (secrets, decrypted-at-rest attributes) beyond the global
     * config('activitylog.default_except_attributes') list.
     *
     * @return list<string>
     */
    protected function auditLogExcept(): array
    {
        return ['created_at', 'updated_at'];
    }
}
