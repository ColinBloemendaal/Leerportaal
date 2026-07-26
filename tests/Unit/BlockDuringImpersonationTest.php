<?php

declare(strict_types=1);

use App\Http\Middleware\BlockDuringImpersonation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

it('blocks the request while impersonating', function (): void {
    $request = Request::create('/whatever', 'POST');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('impersonation_id', 1);

    expect(fn () => (new BlockDuringImpersonation)->handle($request, fn () => new Response))
        ->toThrow(HttpException::class);
});

it('lets the request through when not impersonating', function (): void {
    $request = Request::create('/whatever', 'POST');
    $request->setLaravelSession(app('session.store'));

    $response = (new BlockDuringImpersonation)->handle($request, fn () => new Response('ok'));

    expect($response->getContent())->toBe('ok');
});
