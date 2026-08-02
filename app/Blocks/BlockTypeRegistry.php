<?php

declare(strict_types=1);

namespace App\Blocks;

use App\Blocks\Contracts\BlockType;
use App\Blocks\Types\CalloutBlock;
use App\Blocks\Types\DividerBlock;
use App\Blocks\Types\EmbedBlock;
use App\Blocks\Types\FileDownloadBlock;
use App\Blocks\Types\ImageBlock;
use App\Blocks\Types\RichTextBlock;
use App\Blocks\Types\VideoEmbedBlock;
use App\Enums\BlockTypeEnum;

/**
 * Adding a block type = one BlockTypeEnum case + one entry here + one
 * class implementing BlockType + two Vue components. Nothing else
 * changes -- mirrors CLAUDE.md §5's QuestionTypeRegistry shape.
 */
final class BlockTypeRegistry
{
    /**
     * @var array<string, class-string<BlockType>>
     */
    private const MAP = [
        BlockTypeEnum::RichText->value => RichTextBlock::class,
        BlockTypeEnum::Image->value => ImageBlock::class,
        BlockTypeEnum::VideoEmbed->value => VideoEmbedBlock::class,
        BlockTypeEnum::FileDownload->value => FileDownloadBlock::class,
        BlockTypeEnum::Embed->value => EmbedBlock::class,
        BlockTypeEnum::Divider->value => DividerBlock::class,
        BlockTypeEnum::Callout->value => CalloutBlock::class,
    ];

    public function resolve(BlockTypeEnum $type): BlockType
    {
        return app(self::MAP[$type->value]);
    }
}
