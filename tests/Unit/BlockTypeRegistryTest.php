<?php

declare(strict_types=1);

use App\Blocks\BlockTypeRegistry;
use App\Blocks\Contracts\BlockType;
use App\Blocks\Types\CalloutBlock;
use App\Blocks\Types\DividerBlock;
use App\Blocks\Types\EmbedBlock;
use App\Blocks\Types\FileDownloadBlock;
use App\Blocks\Types\ImageBlock;
use App\Blocks\Types\RichTextBlock;
use App\Blocks\Types\VideoEmbedBlock;
use App\Enums\BlockTypeEnum;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $this->registry = new BlockTypeRegistry;
});

it('resolves every enum case to its definition class', function (BlockTypeEnum $case, string $expectedClass) {
    expect($this->registry->resolve($case))->toBeInstanceOf($expectedClass);
})->with([
    [BlockTypeEnum::RichText, RichTextBlock::class],
    [BlockTypeEnum::Image, ImageBlock::class],
    [BlockTypeEnum::VideoEmbed, VideoEmbedBlock::class],
    [BlockTypeEnum::FileDownload, FileDownloadBlock::class],
    [BlockTypeEnum::Embed, EmbedBlock::class],
    [BlockTypeEnum::Divider, DividerBlock::class],
    [BlockTypeEnum::Callout, CalloutBlock::class],
]);

it('resolves every case to a class implementing BlockType', function (): void {
    foreach (BlockTypeEnum::cases() as $case) {
        expect($this->registry->resolve($case))->toBeInstanceOf(BlockType::class);
    }
});

it('gives every type a non-empty label and component names', function (): void {
    foreach (BlockTypeEnum::cases() as $case) {
        $type = $this->registry->resolve($case);

        expect($type::label())->not->toBe('')
            ->and($type->editorComponent())->toStartWith('Blocks/Editor/')
            ->and($type->playerComponent())->toStartWith('Blocks/Player/');
    }
});
