<?php

declare(strict_types=1);

namespace App\Http\Requests\Courses;

use App\DataTransferObjects\Courses\AssignCourseData;
use App\Tenancy\TenantContext;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCourseAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Same access boundary as the existing courses/assignments index
        // controllers -- see their own comments for why ResellerKlant.
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
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('reseller_id', $resellerId)->whereNotNull('resellerklant_id'),
            ],
        ];
    }

    public function courseId(): int
    {
        return (int) $this->validated('course_id');
    }

    public function toDto(): AssignCourseData
    {
        $userId = $this->user()?->getAuthIdentifier();
        abort_unless(is_int($userId), 401);

        return AssignCourseData::fromArray([
            'user_id' => $this->validated('user_id'),
            'assigned_by_user_id' => $userId,
        ]);
    }
}
