<?php

declare(strict_types=1);

namespace App\Http\Requests\Filtering;

use App\DataTransferObjects\Filtering\CreateSavedFilterData;
use App\Enums\FilterableResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class StoreSavedFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', 'App\Models\SavedFilter') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'resource_type' => ['required', new Enum(FilterableResource::class)],
            'name' => ['required', 'string', 'max:255'],
            'filters' => ['required', 'array'],
        ];
    }

    public function toDto(): CreateSavedFilterData
    {
        /** @var int $userId */
        $userId = $this->user()?->id;

        return new CreateSavedFilterData(
            userId: $userId,
            resourceType: FilterableResource::from((string) $this->input('resource_type')),
            name: (string) $this->input('name'),
            filters: (array) $this->input('filters'),
        );
    }
}
