<?php

declare(strict_types=1);

namespace App\Listeners\Tenancy;

use App\Actions\Tenancy\IssueLetsEncryptCertificate;
use App\Events\Tenancy\CustomDomainVerified;

final readonly class RequestLetsEncryptCertificate
{
    public function __construct(private IssueLetsEncryptCertificate $issueCertificate) {}

    public function handle(CustomDomainVerified $event): void
    {
        ($this->issueCertificate)($event->customDomain);
    }
}
