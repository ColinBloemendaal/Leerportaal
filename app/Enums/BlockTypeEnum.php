<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The fixed set of block types a lesson can be built from. Named with
 * the Enum suffix (not just BlockType) to avoid colliding with
 * App\Blocks\Contracts\BlockType, the per-type definition interface --
 * same split CLAUDE.md §5 uses for questions (QuestionType interface,
 * QuestionTypeEnum enum). Resolved to its definition class via
 * App\Blocks\BlockTypeRegistry.
 */
enum BlockTypeEnum: string
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
