<?php

declare(strict_types=1);

use Tests\Architecture\Support;

it('exposes only __invoke as a public entry point', function (): void {
    $classes = Support::classesUnder(Support::appPath('Actions'), 'App\Actions');

    foreach ($classes as $class) {
        $reflection = new ReflectionClass($class);
        $publicMethods = array_diff(Support::ownPublicMethods($reflection), ['__construct']);

        expect($publicMethods)
            ->toBe(['__invoke'], "{$class} must expose only __invoke, found: ".implode(', ', $publicMethods));
    }
})->skip(fn () => Support::classesUnder(Support::appPath('Actions'), 'App\Actions') === [], 'no actions yet');
