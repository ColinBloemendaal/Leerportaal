<?php

declare(strict_types=1);

namespace App\Services\Dns;

use App\Contracts\Dns\DnsResolver;

final class NativeDnsResolver implements DnsResolver
{
    public function resolveCnameTargets(string $hostname): array
    {
        $records = @dns_get_record($hostname, DNS_CNAME);

        if ($records === false) {
            return [];
        }

        $targets = [];

        foreach ($records as $record) {
            if (isset($record['target']) && is_string($record['target'])) {
                $targets[] = rtrim($record['target'], '.');
            }
        }

        return $targets;
    }
}
