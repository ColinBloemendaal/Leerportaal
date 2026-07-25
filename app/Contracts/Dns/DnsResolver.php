<?php

declare(strict_types=1);

namespace App\Contracts\Dns;

interface DnsResolver
{
    /**
     * @return array<int, string> CNAME targets for the hostname, without a trailing dot.
     */
    public function resolveCnameTargets(string $hostname): array;
}
