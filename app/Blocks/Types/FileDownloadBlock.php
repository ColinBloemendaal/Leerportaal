<?php

declare(strict_types=1);

namespace App\Blocks\Types;

use App\Blocks\Contracts\BlockType;

final class FileDownloadBlock implements BlockType
{
    public static function label(): string
    {
        return 'File / download';
    }

    /**
     * @return array<string, mixed>
     */
    public function contentRules(): array
    {
        return [
            'url' => ['required', 'string', 'url', 'max:2048'],
            'label' => ['required', 'string', 'max:255'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Blocks/Editor/FileDownloadBlock';
    }

    public function playerComponent(): string
    {
        return 'Blocks/Player/FileDownloadBlock';
    }
}
