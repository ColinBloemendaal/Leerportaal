<?php

declare(strict_types=1);

use App\Services\Mail\SafeMarkdownRenderer;

beforeEach(function (): void {
    $this->renderer = new SafeMarkdownRenderer;
});

it('converts a heading and bold text to HTML', function (): void {
    $html = $this->renderer->toHtml("# Heading\n\n**bold** text");

    expect($html)->toContain('<h1>Heading</h1>')
        ->and($html)->toContain('<strong>bold</strong>');
});

it('converts a markdown link to a safe anchor tag', function (): void {
    $html = $this->renderer->toHtml('[Accept invitation](https://example.com/accept)');

    expect($html)->toContain('<a href="https://example.com/accept">Accept invitation</a>');
});

it('escapes a raw script tag instead of emitting it live', function (): void {
    $html = $this->renderer->toHtml('<script>alert(1)</script>');

    expect($html)->not->toContain('<script>')
        ->and($html)->toContain('&lt;script&gt;');
});

it('escapes a raw html event handler attribute', function (): void {
    $html = $this->renderer->toHtml('<img src=x onerror="alert(1)">');

    expect($html)->not->toContain('<img src=x onerror');
});

it('rejects unsafe link schemes', function (): void {
    $html = $this->renderer->toHtml('[click me](javascript:alert(1))');

    expect($html)->not->toContain('href="javascript:alert(1)"');
});
