<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ?User $user
 * @property ?Course $course
 * @property AssignmentBillingState $billing_state
 * @property ?Money $price_cents
 * @property Carbon $assigned_at
 * @property ?Carbon $deadline_at
 */
final class CourseAssignmentIndexResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cursist_name' => $this->user?->name,
            'course_title' => $this->course?->title,
            'billing_state' => $this->billing_state->value,
            'price_cents' => $this->price_cents === null ? 0 : $this->price_cents->cents,
            'assigned_at' => $this->assigned_at->toIso8601String(),
            'deadline_at' => $this->deadline_at?->toIso8601String(),
        ];
    }
}
