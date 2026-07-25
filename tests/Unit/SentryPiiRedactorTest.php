<?php

declare(strict_types=1);

use App\Support\SentryPiiRedactor;
use Sentry\Event;
use Sentry\UserDataBag;

it('strips the user from the event', function (): void {
    $event = Event::createEvent();
    $event->setUser(UserDataBag::createFromArray(['email' => 'cursist@example.test']));

    $result = SentryPiiRedactor::handle($event);

    expect($result->getUser())->toBeNull();
});

it('redacts known PII fields from request data, including nested arrays', function (): void {
    $event = Event::createEvent();
    $event->setRequest([
        'url' => 'https://leerportaal.test/login',
        'data' => [
            'email' => 'cursist@example.test',
            'password' => 'secret',
            'nested' => ['token' => 'abc123'],
        ],
    ]);

    $result = SentryPiiRedactor::handle($event);
    $request = $result->getRequest();

    expect($request['url'])->toBe('https://leerportaal.test/login')
        ->and($request['data']['email'])->toBe('[redacted]')
        ->and($request['data']['password'])->toBe('[redacted]')
        ->and($request['data']['nested']['token'])->toBe('[redacted]');
});

it('redacts known PII fields from extra context', function (): void {
    $event = Event::createEvent();
    $event->setExtra(['bsn' => '123456789', 'unrelated' => 'kept']);

    $result = SentryPiiRedactor::handle($event);
    $extra = $result->getExtra();

    expect($extra['bsn'])->toBe('[redacted]')
        ->and($extra['unrelated'])->toBe('kept');
});
