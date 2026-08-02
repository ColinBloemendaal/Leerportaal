<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The only external video hosts a video_embed block may reference --
 * CLAUDE.md §1 "external hosting only, no self-hosted video". Enforced
 * by App\Rules\AllowedVideoProvider, not just documented here.
 */
enum VideoProvider: string
{
    case Vimeo = 'vimeo';
    case Mux = 'mux';
    case YouTube = 'youtube';

    public function label(): string
    {
        return match ($this) {
            self::Vimeo => 'Vimeo',
            self::Mux => 'Mux',
            self::YouTube => 'YouTube (unlisted)',
        };
    }

    /**
     * @var array<string, self>
     */
    private const HOST_FRAGMENTS = [
        'vimeo.com' => self::Vimeo,
        'mux.com' => self::Mux,
        'youtube.com' => self::YouTube,
        'youtu.be' => self::YouTube,
    ];

    public static function fromUrl(string $url): ?self
    {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null || $host === false) {
            return null;
        }

        foreach (self::HOST_FRAGMENTS as $fragment => $provider) {
            if (str_ends_with($host, $fragment)) {
                return $provider;
            }
        }

        return null;
    }
}
