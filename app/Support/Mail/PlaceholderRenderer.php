<?php

declare(strict_types=1);

namespace App\Support\Mail;

/**
 * Plain string substitution against a fixed, caller-supplied whitelist --
 * deliberately never a templating engine (Blade, Twig, etc.) on
 * reseller-authored text, since that would let override content reach
 * arbitrary PHP execution via directives like `@php`/`@include`.
 */
final class PlaceholderRenderer
{
    /**
     * @param  array<string, string>  $values  token => replacement, keyed
     *                                         the same as the type's
     *                                         MailTemplateType::placeholders()
     */
    public static function render(string $template, array $values): string
    {
        foreach ($values as $token => $value) {
            // preg_replace_callback, not preg_replace: the replacement
            // value is arbitrary text (e.g. an invitee's name) that could
            // itself contain "$1"-style sequences preg_replace would
            // misinterpret as backreferences.
            $template = preg_replace_callback(
                '/\{\{\s*'.preg_quote($token, '/').'\s*\}\}/',
                static fn (): string => $value,
                $template,
            ) ?? $template;
        }

        return $template;
    }
}
