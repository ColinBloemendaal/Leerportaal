<?php

declare(strict_types=1);

use App\Models\FailedJob;
use App\Repositories\Eloquent\EloquentFailedJobRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('counts all failed jobs and only those since a given time', function (): void {
    FailedJob::factory()->create(['failed_at' => now()->subDays(2)]);
    FailedJob::factory()->create(['failed_at' => now()->subHours(2)]);

    $repository = new EloquentFailedJobRepository;

    expect($repository->count())->toBe(2)
        ->and($repository->countSince(now()->subDay()))->toBe(1);
});

it('returns the most recent failed jobs first', function (): void {
    $old = FailedJob::factory()->create(['failed_at' => now()->subDays(2)]);
    $new = FailedJob::factory()->create(['failed_at' => now()]);

    $recent = (new EloquentFailedJobRepository)->recent(10);

    expect($recent->first()?->is($new))->toBeTrue()
        ->and($recent->last()?->is($old))->toBeTrue();
});
