<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The fixed set of block types a lesson can be built from. Per-type
 * editor/player components and `content` payload validation are the
 * "Block types" task -- this enum just names what the `blocks` table's
 * `type` column can hold.
 */
enum BlockType: string
{
    case RichText = 'rich_text';
    case Image = 'image';
    case VideoEmbed = 'video_embed';
    case FileDownload = 'file_download';
    case Embed = 'embed';
    case Divider = 'divider';
    case Callout = 'callout';

    public function label(): string
    {
        return match ($this) {
            self::RichText => 'Rich text',
            self::Image => 'Image',
            self::VideoEmbed => 'Video embed',
            self::FileDownload => 'File / download',
            self::Embed => 'Embed',
            self::Divider => 'Divider',
            self::Callout => 'Callout',
        };
    }
}
