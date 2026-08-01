<?php

declare(strict_types=1);

use App\Support\Mail\PlaceholderRenderer;

it('substitutes a placeholder token', function (): void {
    expect(PlaceholderRenderer::render('Hello {{ name }}!', ['name' => 'Bob']))->toBe('Hello Bob!');
});

it('tolerates no whitespace inside the braces', function (): void {
    expect(PlaceholderRenderer::render('Hello {{name}}!', ['name' => 'Bob']))->toBe('Hello Bob!');
});

it('substitutes multiple distinct tokens', function (): void {
    $result = PlaceholderRenderer::render('{{ a }} and {{ b }}', ['a' => 'X', 'b' => 'Y']);

    expect($result)->toBe('X and Y');
});

it('replaces every occurrence of the same token', function (): void {
    expect(PlaceholderRenderer::render('{{ name }}, {{ name }}!', ['name' => 'Bob']))->toBe('Bob, Bob!');
});

it('leaves unknown tokens untouched', function (): void {
    expect(PlaceholderRenderer::render('{{ unknown }}', ['name' => 'Bob']))->toBe('{{ unknown }}');
});

it('does not misinterpret a replacement value containing a dollar-digit sequence', function (): void {
    expect(PlaceholderRenderer::render('Price: {{ name }}', ['name' => '$1 million']))->toBe('Price: $1 million');
});

it('does not misinterpret a replacement value containing backslashes', function (): void {
    expect(PlaceholderRenderer::render('{{ name }}', ['name' => 'C:\\Users\\Bob']))->toBe('C:\\Users\\Bob');
});
