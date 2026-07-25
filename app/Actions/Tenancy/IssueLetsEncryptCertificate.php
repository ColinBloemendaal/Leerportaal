<?php

declare(strict_types=1);

namespace App\Actions\Tenancy;

use App\Contracts\Ploi\PloiClient;
use App\Enums\CustomDomainStatus;
use App\Models\CustomDomain;
use RuntimeException;

final readonly class IssueLetsEncryptCertificate
{
    public function __construct(private PloiClient $ploi) {}

    public function __invoke(CustomDomain $customDomain): void
    {
        if ($customDomain->status !== CustomDomainStatus::Verified) {
            throw new RuntimeException("Cannot request a certificate for unverified domain {$customDomain->domain}.");
        }

        $this->ploi->requestCertificate($customDomain->domain);
    }
}
