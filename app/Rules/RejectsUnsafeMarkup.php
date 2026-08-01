<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects (doesn't silently strip) constructs that would let
 * user-authored text escape its surrounding tag or exfiltrate data --
 * CLAUDE.md §7. Used for both the theme's custom CSS field (escaping a
 * <style> block) and reseller mail template bodies (escaping whatever
 * markup wraps the rendered email) -- the vectors that matter are the
 * same regardless of which context the text ends up in. Rejection over
 * silent stripping is deliberate: quietly mangling what someone typed
 * is worse UX than telling them why it was refused, and safer than
 * leaving a partially-stripped tag around.
 *
 * Not a full parser -- a denylist of the specific vectors that matter
 * for text rendered inside a raw HTML fragment: breaking out of the
 * surrounding tag entirely, loading external stylesheets/resources, and
 * the legacy IE script-execution vectors.
 */
final class RejectsUnsafeMarkup implements ValidationRule
{
    private const DANGEROUS_PATTERNS = [
        '/<\/style/i' => 'must not contain a closing </style> tag',
        '/<script/i' => 'must not contain a <script> tag',
        '/@import/i' => 'must not use @import',
        '/expression\s*\(/i' => 'must not use expression()',
        '/behavior\s*:/i' => 'must not use the behavior property',
        '/javascript\s*:/i' => 'must not reference javascript: URIs',
        '/vbscript\s*:/i' => 'must not reference vbscript: URIs',
        '/data\s*:\s*text\/html/i' => 'must not reference data:text/html URIs',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $reason = $this->firstViolation($value);

        if ($reason !== null) {
            $fail("The :attribute {$reason}.");
        }
    }

    /**
     * Exposed separately so App\Services\Theming\ThemeCssGenerator can
     * re-run the same denylist as a defense-in-depth check against
     * already-stored values, without needing a Closure shaped like
     * FormRequest validation's $fail callback.
     */
    public function firstViolation(string $value): ?string
    {
        foreach (self::DANGEROUS_PATTERNS as $pattern => $reason) {
            if (preg_match($pattern, $value) === 1) {
                return $reason;
            }
        }

        return null;
    }
}
