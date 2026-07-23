<?php

declare(strict_types=1);

use Tests\Architecture\Support;

it('implements an interface under App\Contracts\Repositories', function (): void {
    $classes = Support::classesUnder(Support::appPath('Repositories/Eloquent'), 'App\Repositories\Eloquent');

    foreach ($classes as $class) {
        $reflection = new ReflectionClass($class);

        $contractInterfaces = array_filter(
            $reflection->getInterfaceNames(),
            fn (string $interface): bool => str_starts_with($interface, 'App\Contracts\Repositories\\'),
        );

        expect($contractInterfaces)
            ->not->toBeEmpty("{$class} must implement an interface under App\\Contracts\\Repositories.");
    }
})->skip(
    fn () => Support::classesUnder(Support::appPath('Repositories/Eloquent'), 'App\Repositories\Eloquent') === [],
    'no repositories yet',
);
