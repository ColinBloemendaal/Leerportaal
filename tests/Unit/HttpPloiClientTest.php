<?php

declare(strict_types=1);

use App\Services\Ploi\HttpPloiClient;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('posts a letsencrypt certificate request to the expected endpoint', function (): void {
    Http::fake(['ploi.io/*' => Http::response(['id' => 1], 200)]);

    $client = new HttpPloiClient(app(HttpFactory::class), 'test-api-key', 'server-1', 'site-1');
    $client->requestCertificate('lms.acme.test');

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://ploi.io/api/servers/server-1/sites/site-1/certificates'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer test-api-key')
            && $request['type'] === 'letsencrypt'
            && $request['certificate'] === 'lms.acme.test';
    });
});

it('throws when the request fails', function (): void {
    Http::fake(['ploi.io/*' => Http::response(['message' => 'nope'], 422)]);

    $client = new HttpPloiClient(app(HttpFactory::class), 'test-api-key', 'server-1', 'site-1');

    expect(fn () => $client->requestCertificate('lms.acme.test'))
        ->toThrow(RuntimeException::class);
});
