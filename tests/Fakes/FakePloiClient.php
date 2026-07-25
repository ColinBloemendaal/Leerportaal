<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Contracts\Ploi\PloiClient;

final class FakePloiClient implements PloiClient
{
    /**
     * @var array<int, string>
     */
    private array $requestedCertificates = [];

    public function requestCertificate(string $domain): void
    {
        $this->requestedCertificates[] = $domain;
    }

    public function requestedCertificateFor(string $domain): bool
    {
        return in_array($domain, $this->requestedCertificates, true);
    }

    /**
     * @return array<int, string>
     */
    public function requestedCertificates(): array
    {
        return $this->requestedCertificates;
    }
}
