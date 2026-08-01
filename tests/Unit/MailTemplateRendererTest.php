<?php

declare(strict_types=1);

use App\Models\ResellerMailTemplate;
use App\Services\Mail\MailTemplateRenderer;
use App\Services\Mail\SafeMarkdownRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->renderer = new MailTemplateRenderer(new SafeMarkdownRenderer);
});

it('falls back to the default subject when there is no override', function (): void {
    $subject = $this->renderer->renderSubject(null, 'Default subject for {{ reseller_name }}', [
        'reseller_name' => 'Acme',
    ]);

    expect($subject)->toBe('Default subject for Acme');
});

it('uses the override subject with placeholders substituted', function (): void {
    $override = ResellerMailTemplate::factory()->make(['subject' => 'Welcome to {{ reseller_name }}!']);

    $subject = $this->renderer->renderSubject($override, 'unused default', ['reseller_name' => 'Acme']);

    expect($subject)->toBe('Welcome to Acme!');
});

it('renders the override body as safe HTML with placeholders substituted', function (): void {
    $override = ResellerMailTemplate::factory()->make([
        'body_markdown' => "# Hi {{ invitee_name }}\n\n[Accept]({{ accept_url }})",
    ]);

    $content = $this->renderer->renderOverrideContent($override, [
        'invitee_name' => 'Bob',
        'accept_url' => 'https://example.com/accept',
    ], 'Acme');

    expect($content->htmlString)->toContain('<h1>Hi Bob</h1>')
        ->and($content->htmlString)->toContain('<a href="https://example.com/accept">Accept</a>')
        ->and($content->htmlString)->toContain('Acme');
});

it('escapes unsafe markdown in the override body rather than executing it', function (): void {
    $override = ResellerMailTemplate::factory()->make([
        'body_markdown' => '<script>alert(1)</script>',
    ]);

    $content = $this->renderer->renderOverrideContent($override, [], 'Acme');

    expect($content->htmlString)->not->toContain('<script>alert(1)</script>');
});
