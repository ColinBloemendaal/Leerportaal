<?php

declare(strict_types=1);

namespace App\Http\Requests\Exporting;

use App\DataTransferObjects\Exporting\RequestExportData;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\FilterableResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * authorize() only checks that resource_type is a real, known resource
 * -- the actual "may this user export this kind of data" check depends
 * on which resource it is (different areas, different policies), so
 * it's done in ExportController itself, same reasoning as
 * StartImpersonationRequest for its own target-dependent check.
 */
final class StoreExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'resource_type' => ['required', new Enum(FilterableResource::class)],
        ];
    }

    public function toDto(int $userId, ?int $resellerId): RequestExportData
    {
        $filters = FilterRequestData::fromRequest($this);

        return new RequestExportData(
            userId: $userId,
            resellerId: $resellerId,
            resourceType: FilterableResource::from((string) $this->validated('resource_type')),
            filters: [
                'search' => $filters->search,
                'sort' => $filters->sort,
                'sortDirection' => $filters->sortDirection,
                'filters' => $filters->filters,
            ],
        );
    }
}
