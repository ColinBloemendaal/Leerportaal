<?php

declare(strict_types=1);

namespace App\Services\Ploi;

use App\Contracts\Ploi\PloiClient;
use Illuminate\Http\Client\Factory as HttpFactory;
use RuntimeException;

/**
 * VERIFY: endpoint path and payload shape against Ploi's current API docs
 * once real credentials exist -- written to best available understanding
 * of their API, not verified against a live account. See docs/deployment.md.
 */
final class HttpPloiClient implements PloiClient
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $apiKey,
        private readonly string $serverId,
        private readonly string $siteId,
    ) {}

    public function requestCertificate(string $domain): void
    {
        $response = $this->http
            ->withToken($this->apiKey)
            ->asJson()
            ->post(
                "https://ploi.io/api/servers/{$this->serverId}/sites/{$this->siteId}/certificates",
                [
                    'type' => 'letsencrypt',
                    'certificate' => $domain,
                ],
            );

        if ($response->failed()) {
            throw new RuntimeException("Ploi certificate request failed for {$domain}: {$response->body()}");
        }
    }
}
