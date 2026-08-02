<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * A real contract for models using Spatie\Translatable\HasTranslations --
 * that's a trait, not an interface, so it can't be type-hinted directly.
 * Models implement this alongside the trait (which already satisfies both
 * method signatures) so callers like
 * App\Services\Translation\TranslationCompletenessService have something
 * to type-hint against.
 */
interface HasTranslatableFields
{
    /**
     * @return list<string>
     */
    public function getTranslatableAttributes(): array;

    /**
     * @param  list<string>|null  $allowedLocales
     * @return array<string, string>
     */
    public function getTranslations(?string $key = null, ?array $allowedLocales = null): array;
}
