<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Models\ResellerMailTemplate;
use App\Support\Mail\PlaceholderRenderer;
use Illuminate\Mail\Mailables\Content;

/**
 * Renders a reseller's Markdown override for a notification -- see
 * App\Enums\MailTemplateType. Deliberately bypasses Illuminate\Mail\Markdown
 * and its `mail::message` Blade components entirely for override content:
 * that pipeline is built for developer-authored views, and reusing it here
 * would mean re-auditing how it handles untrusted Markdown. Override emails
 * get a simpler, fully-custom-controlled wrapper instead
 * (resources/views/emails/templates/overridden.blade.php).
 */
final readonly class MailTemplateRenderer
{
    public function __construct(private SafeMarkdownRenderer $markdown) {}

    /**
     * @param  array<string, string>  $placeholders
     */
    public function renderSubject(?ResellerMailTemplate $override, string $defaultSubject, array $placeholders): string
    {
        $subject = $override === null ? $defaultSubject : $override->subject;

        return PlaceholderRenderer::render($subject, $placeholders);
    }

    /**
     * @param  array<string, string>  $placeholders
     */
    public function renderOverrideContent(
        ResellerMailTemplate $override,
        array $placeholders,
        string $resellerName,
    ): Content {
        $bodyMarkdown = PlaceholderRenderer::render($override->body_markdown, $placeholders);

        $html = view('emails.templates.overridden', [
            'bodyHtml' => $this->markdown->toHtml($bodyMarkdown),
            'resellerName' => $resellerName,
        ])->render();

        return new Content(htmlString: $html);
    }
}
