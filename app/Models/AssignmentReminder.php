<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\TenantScoped;
use App\Enums\NotificationType;
use Database\Factories\AssignmentReminderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dedup record only -- "has a deadline/overdue reminder already gone out
 * for this assignment (and offset)". Not itself a notification; the
 * database-channel notification a cursist actually sees lives in
 * Laravel's own notifications table via Notifiable.
 */
final class AssignmentReminder extends Model
{
    /** @use HasFactory<AssignmentReminderFactory> */
    use HasFactory;

    use TenantScoped;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'sent_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<CourseAssignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(CourseAssignment::class, 'course_assignment_id');
    }
}
