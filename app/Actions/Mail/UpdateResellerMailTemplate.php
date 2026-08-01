<?php

declare(strict_types=1);

namespace App\Actions\Mail;

use App\DataTransferObjects\Mail\UpdateResellerMailTemplateData;
use App\Models\ResellerMailTemplate;
use App\Tenancy\TenantContext;

/**
 * subject and body_markdown are both-or-neither on the row (see the
 * migration): the FormRequest enforces both-or-neither on the way in, so
 * "both empty" here means "reset to default" -- the override row is
 * deleted rather than left around empty.
 */
final readonly class UpdateResellerMailTemplate
{
    public function __construct(private TenantContext $tenantContext) {}

    public function __invoke(UpdateResellerMailTemplateData $data): ?ResellerMailTemplate
    {
        $resellerId = $this->tenantContext->id();

        if ($data->subject === null && $data->bodyMarkdown === null) {
            ResellerMailTemplate::query()
                ->where('reseller_id', $resellerId)
                ->where('type', $data->type)
                ->delete();

            return null;
        }

        return ResellerMailTemplate::updateOrCreate(
            ['reseller_id' => $resellerId, 'type' => $data->type],
            ['subject' => $data->subject, 'body_markdown' => $data->bodyMarkdown],
        );
    }
}
