<?php

declare(strict_types=1);

use Tests\Architecture\Support;

it('exposes only the constructor and static named constructors', function (): void {
    $classes = Support::classesUnder(Support::appPath('DataTransferObjects'), 'App\DataTransferObjects');

    foreach ($classes as $class) {
        $reflection = new ReflectionClass($class);

        foreach (Support::ownPublicMethods($reflection) as $method) {
            if ($method === '__construct') {
                continue;
            }

            expect($reflection->getMethod($method)->isStatic())
                ->toBeTrue("{$class}::{$method}() must be a static named constructor, not an instance method.");
        }
    }
})->skip(
    fn () => Support::classesUnder(Support::appPath('DataTransferObjects'), 'App\DataTransferObjects') === [],
    'no DTOs yet',
);
