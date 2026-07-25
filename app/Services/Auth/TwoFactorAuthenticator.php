<?php

declare(strict_types=1);

namespace App\Services\Auth;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Thin wrapper around pragmarx/google2fa (TOTP) and bacon/bacon-qr-code
 * (setup QR code) -- not something to hand-roll, this is real crypto.
 */
final class TwoFactorAuthenticator
{
    public function __construct(private readonly Google2FA $engine) {}

    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->engine->verifyKey($secret, $code) !== false;
    }

    public function qrCodeSvg(string $issuer, string $holder, string $secret): string
    {
        $url = $this->engine->getQRCodeUrl($issuer, $holder, $secret);

        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($url);
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect()
            ->times($count, fn () => Str::random(10).'-'.Str::random(10))
            ->all();
    }
}
