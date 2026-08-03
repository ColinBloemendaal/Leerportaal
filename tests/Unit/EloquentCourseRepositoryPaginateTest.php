<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Reseller;
use App\Repositories\Eloquent\EloquentCourseRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('paginates catalog courses plus the current reseller\'s own, filtered by status', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    Course::factory()->published()->create(); // catalog
    Course::factory()->forReseller($reseller->id)->published()->create();
    Course::factory()->forReseller($reseller->id)->create(['status' => CourseStatus::Draft]);
    Course::factory()->forReseller($otherReseller->id)->published()->create();

    $repository = app(EloquentCourseRepository::class);
    $filters = new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: ['status' => CourseStatus::Published->value]);

    $result = $repository->paginate($filters);

    expect($result->total())->toBe(2);
});
