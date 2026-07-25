<?php

declare(strict_types=1);

namespace App\Actions\Tenancy;

use App\Contracts\Dns\DnsResolver;
use App\Enums\CustomDomainStatus;
use App\Models\CustomDomain;

final readonly class VerifyCustomDomain
{
    public function __construct(private DnsResolver $dns) {}

    public function __invoke(CustomDomain $customDomain): CustomDomain
    {
        $target = rtrim((string) config('tenancy.custom_domain_target'), '.');
        $targets = $this->dns->resolveCnameTargets($customDomain->domain);
        $verified = in_array($target, $targets, true);

        $customDomain->update([
            'status' => $verified ? CustomDomainStatus::Verified : CustomDomainStatus::Failed,
            'verified_at' => $verified ? now() : null,
        ]);

        return $customDomain->refresh();
    }
}
