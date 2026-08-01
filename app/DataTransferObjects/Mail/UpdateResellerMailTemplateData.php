<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Mail;

use App\Enums\MailTemplateType;

final readonly class UpdateResellerMailTemplateData
{
    public function __construct(
        public MailTemplateType $type,
        public ?string $subject,
        public ?string $bodyMarkdown,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(MailTemplateType $type, array $data): self
    {
        return new self(
            type: $type,
            subject: $data['subject'] ?? null,
            bodyMarkdown: $data['body_markdown'] ?? null,
        );
    }
}
