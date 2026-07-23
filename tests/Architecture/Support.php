<?php

declare(strict_types=1);

namespace Tests\Architecture;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

final class Support
{
    /**
     * Resolves a path under app/ without relying on Laravel's container
     * (these architecture tests do not boot the framework).
     */
    public static function appPath(string $relative): string
    {
        return dirname(__DIR__, 2).'/app/'.ltrim($relative, '/');
    }

    /**
     * @return array<int, class-string>
     */
    public static function classesUnder(string $directory, string $namespace): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $classes = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($directory) + 1);
            $class = $namespace.'\\'.str_replace(['/', '\\', '.php'], ['\\', '\\', ''], $relative);
            $class = rtrim($class, '\\');

            if (class_exists($class) || interface_exists($class) || enum_exists($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Public method names declared on the class itself (not inherited).
     *
     * @return array<int, string>
     */
    public static function ownPublicMethods(ReflectionClass $class): array
    {
        $methods = [];

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }

            $methods[] = $method->getName();
        }

        return $methods;
    }
}
