<?php

declare(strict_types=1);

namespace App\Contracts\Ploi;

interface PloiClient
{
    /**
     * Requests a Let's Encrypt certificate covering the given domain on
     * the configured Ploi site.
     */
    public function requestCertificate(string $domain): void;
}
