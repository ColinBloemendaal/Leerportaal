<?php

declare(strict_types=1);

use App\Rules\RejectsUnsafeMarkup;

function firstMarkupViolation(string $value): ?string
{
    return (new RejectsUnsafeMarkup)->firstViolation($value);
}

it('passes ordinary css', function (): void {
    expect(firstMarkupViolation('.btn { border-radius: 0; color: #fff; }'))->toBeNull();
});

it('passes ordinary markdown', function (): void {
    expect(firstMarkupViolation("# Heading\n\nSome **bold** text and a [link](https://example.com)."))->toBeNull();
});

it('rejects a closing style tag', function (): void {
    expect(firstMarkupViolation('body {} </style><script>alert(1)</script>'))->not->toBeNull();
});

it('rejects a script tag', function (): void {
    expect(firstMarkupViolation('<script>alert(1)</script>'))->not->toBeNull();
});

it('rejects @import', function (): void {
    expect(firstMarkupViolation('@import url(https://evil.example/x.css);'))->not->toBeNull();
});

it('rejects expression()', function (): void {
    expect(firstMarkupViolation('.x { width: expression(alert(1)); }'))->not->toBeNull();
});

it('rejects the legacy IE behavior property', function (): void {
    expect(firstMarkupViolation('.x { behavior: url(x.htc); }'))->not->toBeNull();
});

it('rejects javascript: URIs', function (): void {
    expect(firstMarkupViolation('.x { background: url(javascript:alert(1)); }'))->not->toBeNull();
});

it('rejects vbscript: URIs', function (): void {
    expect(firstMarkupViolation('.x { background: url(vbscript:alert(1)); }'))->not->toBeNull();
});

it('rejects data:text/html URIs', function (): void {
    expect(firstMarkupViolation('.x { background: url(data:text/html;base64,x); }'))->not->toBeNull();
});

it('is case-insensitive', function (): void {
    expect(firstMarkupViolation('<SCRIPT>alert(1)</SCRIPT>'))->not->toBeNull();
});
