<?php

declare(strict_types=1);

use App\Rules\SafeCustomCss;

function validateCustomCss(string $value): ?string
{
    return (new SafeCustomCss)->firstViolation($value);
}

it('passes ordinary css', function (): void {
    expect(validateCustomCss('.btn { border-radius: 0; color: #fff; }'))->toBeNull();
});

it('rejects a closing style tag', function (): void {
    expect(validateCustomCss('body {} </style><script>alert(1)</script>'))->not->toBeNull();
});

it('rejects a script tag', function (): void {
    expect(validateCustomCss('<script>alert(1)</script>'))->not->toBeNull();
});

it('rejects @import', function (): void {
    expect(validateCustomCss('@import url(https://evil.example/x.css);'))->not->toBeNull();
});

it('rejects expression()', function (): void {
    expect(validateCustomCss('.x { width: expression(alert(1)); }'))->not->toBeNull();
});

it('rejects the legacy IE behavior property', function (): void {
    expect(validateCustomCss('.x { behavior: url(x.htc); }'))->not->toBeNull();
});

it('rejects javascript: URIs', function (): void {
    expect(validateCustomCss('.x { background: url(javascript:alert(1)); }'))->not->toBeNull();
});

it('rejects vbscript: URIs', function (): void {
    expect(validateCustomCss('.x { background: url(vbscript:alert(1)); }'))->not->toBeNull();
});

it('rejects data:text/html URIs', function (): void {
    expect(validateCustomCss('.x { background: url(data:text/html;base64,x); }'))->not->toBeNull();
});

it('is case-insensitive', function (): void {
    expect(validateCustomCss('<SCRIPT>alert(1)</SCRIPT>'))->not->toBeNull();
});
