<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Contracts\Dns\DnsResolver;

final class FakeDnsResolver implements DnsResolver
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $records = [];

    /**
     * @param  array<int, string>  $targets
     */
    public function setCnameTargets(string $hostname, array $targets): void
    {
        $this->records[$hostname] = $targets;
    }

    public function resolveCnameTargets(string $hostname): array
    {
        return $this->records[$hostname] ?? [];
    }
}
