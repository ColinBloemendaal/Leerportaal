<?php

declare(strict_types=1);

namespace App\Services\Translation;

use App\Contracts\HasTranslatableFields;

/**
 * Whether a model's translatable fields are all filled in for a given
 * locale -- the "translation completeness" check. Enforcing "don't
 * publish a course in a locale until its translations are complete" is
 * the publish Action's job once the draft/review/published workflow
 * (the next Phase 3 task) exists; this service only answers the
 * question, it doesn't gate anything itself.
 */
final readonly class TranslationCompletenessService
{
    /**
     * @return list<string>
     */
    public function missingFields(HasTranslatableFields $model, string $locale): array
    {
        return array_values(array_filter(
            $model->getTranslatableAttributes(),
            fn (string $field): bool => ! $this->hasValue($model, $field, $locale),
        ));
    }

    public function isComplete(HasTranslatableFields $model, string $locale): bool
    {
        return $this->missingFields($model, $locale) === [];
    }

    private function hasValue(HasTranslatableFields $model, string $field, string $locale): bool
    {
        $translations = $model->getTranslations($field);

        return isset($translations[$locale]) && trim($translations[$locale]) !== '';
    }
}
