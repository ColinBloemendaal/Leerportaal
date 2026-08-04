<?php

declare(strict_types=1);

namespace App\Http\Requests\Courses;

use App\DataTransferObjects\Courses\BulkAssignCourseData;
use App\Tenancy\TenantContext;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BulkStoreCourseAssignmentRequest extends FormRequest
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
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where('reseller_id', $resellerId)->whereNotNull('resellerklant_id'),
            ],
        ];
    }

    public function courseId(): int
    {
        return (int) $this->validated('course_id');
    }

    public function toDto(): BulkAssignCourseData
    {
        $userId = $this->user()?->getAuthIdentifier();
        abort_unless(is_int($userId), 401);

        return BulkAssignCourseData::fromArray([
            'user_ids' => $this->validated('user_ids'),
            'assigned_by_user_id' => $userId,
        ]);
    }
}
