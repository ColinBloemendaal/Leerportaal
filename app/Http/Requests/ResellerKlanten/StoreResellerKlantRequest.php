<?php

declare(strict_types=1);

namespace App\Http\Requests\ResellerKlanten;

use App\DataTransferObjects\ResellerKlanten\CreateResellerKlantData;
use Illuminate\Foundation\Http\FormRequest;

final class StoreResellerKlantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', 'App\Models\ResellerKlant') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function toDto(): CreateResellerKlantData
    {
        return CreateResellerKlantData::fromArray($this->validated());
    }
}
