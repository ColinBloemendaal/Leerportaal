<?php

declare(strict_types=1);

namespace App\Actions\Theming;

use App\DataTransferObjects\Theming\UpdateResellerThemeData;
use App\Models\ResellerTheme;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Assets are stored on the private disk ('local'), not the public one --
 * CLAUDE.md §7 requires private-by-default storage even though these
 * particular files (logo/favicon/login background) are shown on the
 * public login page. App\Http\Controllers\ResellerBrandingController is
 * the public, unauthenticated route that streams them back out, so the
 * disk itself never needs a directly guessable public URL.
 */
final readonly class UpdateResellerTheme
{
    private const DISK = 'local';

    public function __construct(
        private TenantContext $tenantContext,
        private FilesystemFactory $filesystem,
    ) {}

    public function __invoke(UpdateResellerThemeData $data): ResellerTheme
    {
        $resellerId = $this->tenantContext->id();

        $theme = ResellerTheme::updateOrCreate(
            ['reseller_id' => $resellerId],
            [
                'primary_color' => $data->primaryColor,
                'secondary_color' => $data->secondaryColor,
                'accent_color' => $data->accentColor,
                'font_family' => $data->fontFamily,
                'custom_css' => $data->customCss,
                'sender_name' => $data->senderName,
                'reply_to_email' => $data->replyToEmail,
                'footer_text' => $data->footerText,
                'support_email' => $data->supportEmail,
                'terms_url' => $data->termsUrl,
                'privacy_url' => $data->privacyUrl,
            ],
        );

        $updates = array_filter([
            'logo_path' => $this->storeAsset($resellerId, 'logo', $data->logo, $theme->logo_path),
            'favicon_path' => $this->storeAsset($resellerId, 'favicon', $data->favicon, $theme->favicon_path),
            'login_background_path' => $this->storeAsset(
                $resellerId,
                'login-background',
                $data->loginBackground,
                $theme->login_background_path,
            ),
        ], fn (?string $path): bool => $path !== null);

        if ($updates !== []) {
            $theme->update($updates);
        }

        return $theme->fresh() ?? $theme;
    }

    /**
     * Null if no new file was uploaded (nothing to update). Deletes the
     * previous file first so replacing a .png logo with a .jpg one, say,
     * doesn't leave an orphaned file behind under the old extension.
     */
    private function storeAsset(?int $resellerId, string $name, ?UploadedFile $file, ?string $previousPath): ?string
    {
        if ($file === null) {
            return null;
        }

        $disk = $this->filesystem->disk(self::DISK);

        if ($previousPath !== null) {
            $disk->delete($previousPath);
        }

        $filename = $name.'.'.$file->extension();
        $stored = $file->storeAs("reseller-themes/{$resellerId}", $filename, self::DISK);

        if ($stored === false) {
            throw new RuntimeException("Failed to store uploaded {$name} on disk.");
        }

        return $stored;
    }
}
