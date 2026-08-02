<?php

declare(strict_types=1);

use App\Actions\Filtering\DeleteSavedFilter;
use App\Models\SavedFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('soft deletes the saved filter', function (): void {
    $savedFilter = SavedFilter::factory()->create();

    app(DeleteSavedFilter::class)($savedFilter);

    expect(SavedFilter::query()->find($savedFilter->id))->toBeNull();
});
