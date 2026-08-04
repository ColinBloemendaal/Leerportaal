<?php

declare(strict_types=1);

namespace App\Http\Requests\Courses;

use App\DataTransferObjects\Courses\GroupAssignCourseData;
use App\Tenancy\TenantContext;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GroupStoreCourseAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', 'App\Models\ResellerKlant') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $resellerId = app(TenantContext::class)->id();

        return [
            'course_id' => [
                'required',
                'integer',
                Rule::exists('courses', 'id')->where(function (Builder $query) use ($resellerId): void {
                    $query->whereNull('reseller_id')->orWhere('reseller_id', $resellerId);
                }),
            ],
            'group_id' => [
                'required',
                'integer',
                Rule::exists('groups', 'id')->where('reseller_id', $resellerId)->whereNull('deleted_at'),
            ],
        ];
    }

    public function courseId(): int
    {
        return (int) $this->validated('course_id');
    }

    public function groupId(): int
    {
        return (int) $this->validated('group_id');
    }

    public function toDto(): GroupAssignCourseData
    {
        $userId = $this->user()?->getAuthIdentifier();
        abort_unless(is_int($userId), 401);

        return GroupAssignCourseData::fromArray([
            'assigned_by_user_id' => $userId,
        ]);
    }
}
