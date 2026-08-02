<?php

declare(strict_types=1);

use App\Blocks\Types\CalloutBlock;
use App\Blocks\Types\DividerBlock;
use App\Blocks\Types\EmbedBlock;
use App\Blocks\Types\FileDownloadBlock;
use App\Blocks\Types\ImageBlock;
use App\Blocks\Types\RichTextBlock;
use App\Blocks\Types\VideoEmbedBlock;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class);

it('passes valid rich text content', function (): void {
    $validator = Validator::make(['html' => '<p>Hello</p>'], (new RichTextBlock)->contentRules());

    expect($validator->passes())->toBeTrue();
});

it('rejects rich text content that breaks out of its wrapper', function (): void {
    $validator = Validator::make(
        ['html' => '<script>alert(1)</script>'],
        (new RichTextBlock)->contentRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('passes valid image content', function (): void {
    $validator = Validator::make(
        ['url' => 'https://example.com/photo.jpg', 'alt' => 'A photo', 'caption' => null],
        (new ImageBlock)->contentRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects image content missing required alt text', function (): void {
    $validator = Validator::make(
        ['url' => 'https://example.com/photo.jpg'],
        (new ImageBlock)->contentRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('passes valid video embed content', function (): void {
    $validator = Validator::make(
        ['url' => 'https://vimeo.com/123456', 'provider' => 'vimeo'],
        (new VideoEmbedBlock)->contentRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects video embed content with a non-url value', function (): void {
    $validator = Validator::make(['url' => 'not-a-url'], (new VideoEmbedBlock)->contentRules());

    expect($validator->fails())->toBeTrue();
});

it('rejects video embed content pointing at a self-hosted stream', function (): void {
    $validator = Validator::make(
        ['url' => 'https://cdn.example.com/videos/lesson-1.mp4'],
        (new VideoEmbedBlock)->contentRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('rejects video embed content with an unrecognized provider value', function (): void {
    $validator = Validator::make(
        ['url' => 'https://vimeo.com/123456', 'provider' => 'self-hosted'],
        (new VideoEmbedBlock)->contentRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('passes valid file download content', function (): void {
    $validator = Validator::make(
        ['url' => 'https://example.com/handout.pdf', 'label' => 'Handout'],
        (new FileDownloadBlock)->contentRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects file download content missing a label', function (): void {
    $validator = Validator::make(
        ['url' => 'https://example.com/handout.pdf'],
        (new FileDownloadBlock)->contentRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('passes valid embed content', function (): void {
    $validator = Validator::make(['url' => 'https://example.com/widget'], (new EmbedBlock)->contentRules());

    expect($validator->passes())->toBeTrue();
});

it('has no content rules for a divider', function (): void {
    expect((new DividerBlock)->contentRules())->toBe([]);
});

it('passes valid callout content', function (): void {
    $validator = Validator::make(
        ['text' => 'Heads up', 'variant' => 'warning'],
        (new CalloutBlock)->contentRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects a callout with an unknown variant', function (): void {
    $validator = Validator::make(
        ['text' => 'Heads up', 'variant' => 'sparkly'],
        (new CalloutBlock)->contentRules(),
    );

    expect($validator->fails())->toBeTrue();
});
