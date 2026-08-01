<?php

declare(strict_types=1);

namespace App\Services\Theming;

use App\Models\ResellerTheme;
use App\Rules\SafeCustomCss;

/**
 * Renders a reseller's theme as CSS custom properties, injected at
 * runtime (CLAUDE.md §1: "no per-tenant CSS build step"). Bootstrap's
 * own SCSS variables reference these -- see resources/sass/_variables.scss.
 *
 * Every value is re-validated here, independent of whatever validation
 * exists at the point of writing a ResellerTheme row: this is the last
 * line of defense against CSS injection before these strings are
 * interpolated into a raw <style> block, so it must never trust the
 * database contents.
 */
final readonly class ThemeCssGenerator
{
    public function generate(?ResellerTheme $theme): string
    {
        if ($theme === null) {
            return '';
        }

        $declarations = array_filter([
            $this->declaration('--reseller-primary-color', $theme->primary_color, $this->sanitizeColor(...)),
            $this->declaration('--reseller-secondary-color', $theme->secondary_color, $this->sanitizeColor(...)),
            $this->declaration('--reseller-accent-color', $theme->accent_color, $this->sanitizeColor(...)),
            $this->declaration('--reseller-font-family', $theme->font_family, $this->sanitizeFontFamily(...)),
        ]);

        $rootBlock = $declarations === [] ? '' : ':root { '.implode(' ', $declarations).' }';

        $customCss = $this->sanitizeCustomCss($theme->custom_css);

        return trim($rootBlock.($customCss !== null ? ' '.$customCss : ''));
    }

    /**
     * @param  (callable(string): ?string)  $sanitizer
     */
    private function declaration(string $variable, ?string $value, callable $sanitizer): ?string
    {
        if ($value === null) {
            return null;
        }

        $safeValue = $sanitizer($value);

        if ($safeValue === null) {
            return null;
        }

        return "{$variable}: {$safeValue};";
    }

    private function sanitizeColor(string $value): ?string
    {
        return preg_match('/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/', $value) === 1 ? $value : null;
    }

    /**
     * Deliberately conservative: letters, digits, spaces, commas, hyphens,
     * and quotes only -- enough for "Inter, system-ui" style font stacks
     * without allowing arbitrary CSS syntax through.
     */
    private function sanitizeFontFamily(string $value): ?string
    {
        return preg_match('/^[\w\s,\'"-]+$/', $value) === 1 ? $value : null;
    }

    /**
     * Defense in depth: re-runs the same denylist the write-side
     * SafeCustomCss rule applies. The database is never trusted as the
     * sole gate against a value that ends up interpolated into a raw
     * <style> block -- if it somehow fails here, it's dropped silently
     * rather than surfaced, since there is no user to show a validation
     * error to at render time.
     */
    private function sanitizeCustomCss(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (new SafeCustomCss)->firstViolation($value) === null ? $value : null;
    }
}
