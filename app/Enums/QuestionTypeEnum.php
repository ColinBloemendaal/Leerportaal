<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The fixed set of question types a quiz can be built from (CLAUDE.md
 * §5). Named with the Enum suffix to avoid colliding with
 * App\Questions\Contracts\QuestionType, the per-type definition
 * interface -- same split as App\Enums\BlockTypeEnum /
 * App\Blocks\Contracts\BlockType. Resolved to its definition class via
 * App\Questions\QuestionTypeRegistry.
 */
enum QuestionTypeEnum: string
{
    case MultipleChoice = 'multiple_choice';
    case MultipleResponse = 'multiple_response';
    case TrueFalse = 'true_false';
    case OpenShort = 'open_short';
    case Essay = 'essay';
    case Matching = 'matching';
    case Ordering = 'ordering';
    case FillInBlank = 'fill_in_blank';
    case DropdownInText = 'dropdown_in_text';
    case Numeric = 'numeric';
    case HotspotImage = 'hotspot_image';
    case DragDropImage = 'drag_drop_image';
    case Likert = 'likert';
    case FileUpload = 'file_upload';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'Multiple choice',
            self::MultipleResponse => 'Multiple response',
            self::TrueFalse => 'True / false',
            self::OpenShort => 'Open (short answer)',
            self::Essay => 'Essay',
            self::Matching => 'Matching',
            self::Ordering => 'Ordering',
            self::FillInBlank => 'Fill in the blank',
            self::DropdownInText => 'Dropdown in text',
            self::Numeric => 'Numeric',
            self::HotspotImage => 'Hotspot image',
            self::DragDropImage => 'Drag & drop image',
            self::Likert => 'Likert scale',
            self::FileUpload => 'File upload',
        };
    }
}
