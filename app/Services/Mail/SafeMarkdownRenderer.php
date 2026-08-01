<?php

declare(strict_types=1);

namespace App\Services\Mail;

use League\CommonMark\CommonMarkConverter;

/**
 * Converts reseller-authored Markdown to HTML for email bodies.
 *
 * Deliberately does NOT reuse Illuminate\Mail\Markdown: that class is
 * configured for *developer*-authored Blade mail views, not untrusted
 * input. This uses its own CommonMark instance with raw HTML input
 * disabled -- a `<script>` or `<img onerror=...>` in reseller-authored
 * Markdown is escaped to visible text, never emitted as a live tag.
 */
final class SafeMarkdownRenderer
{
    private readonly CommonMarkConverter $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    public function toHtml(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }
}
