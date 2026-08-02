<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class IncompleteCourseTranslationsException extends DomainException
{
    /**
     * @param  list<string>  $locales
     */
    public static function forLocales(array $locales): self
    {
        $list = implode(', ', $locales);

        return new self("Cannot publish: translations are incomplete for locale(s): {$list}.");
    }
}
