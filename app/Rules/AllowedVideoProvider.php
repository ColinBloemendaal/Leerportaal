<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\VideoProvider;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects any video_embed URL that isn't recognizably Vimeo, Mux, or
 * YouTube -- CLAUDE.md §1 "external hosting only, no self-hosted
 * video". The block's content shape already can't hold an uploaded
 * file (url is a plain string, never a file reference), but that alone
 * doesn't stop someone pointing it at an arbitrary self-hosted stream.
 */
final class AllowedVideoProvider implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (VideoProvider::fromUrl($value) === null) {
            $providers = implode(', ', array_map(fn (VideoProvider $p): string => $p->label(), VideoProvider::cases()));
            $fail("The :attribute must be a link to one of: {$providers}.");
        }
    }
}
