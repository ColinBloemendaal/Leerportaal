<?php

declare(strict_types=1);

namespace App\Http\Requests\Mail;

use App\DataTransferObjects\Mail\UpdateResellerMailTemplateData;
use App\Enums\MailTemplateType;
use App\Rules\RejectsUnsafeMarkup;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateResellerMailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', 'App\Models\ResellerMailTemplate') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // A row only exists when both are set (see
            // App\Actions\Mail\UpdateResellerMailTemplate) -- submitting
            // both empty resets to the default, submitting only one is a
            // validation error rather than a half-saved override.
            'subject' => ['nullable', 'string', 'max:255', 'required_with:body_markdown'],

            // Reused from the theme's custom-CSS field: this Markdown
            // ends up rendered into an email body, so the same
            // </style>/<script>/@import-class denylist applies, even
            // though this isn't CSS -- the vector (breaking out of
            // whatever wrapper markup surrounds it) is the same.
            'body_markdown' => ['nullable', 'string', 'max:20000', 'required_with:subject', new RejectsUnsafeMarkup],
        ];
    }

    public function toDto(): UpdateResellerMailTemplateData
    {
        /** @var MailTemplateType $type */
        $type = $this->route('type');

        return UpdateResellerMailTemplateData::fromArray($type, $this->validated());
    }
}
