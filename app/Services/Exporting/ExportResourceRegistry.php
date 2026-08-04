<?php

declare(strict_types=1);

namespace App\Services\Exporting;

use App\Contracts\Repositories\ActivityLogRepository;
use App\Contracts\Repositories\CourseAssignmentRepository;
use App\Contracts\Repositories\CourseRepository;
use App\Contracts\Repositories\InvoiceRepository;
use App\Contracts\Repositories\QuizAttemptRepository;
use App\Contracts\Repositories\ResellerKlantRepository;
use App\Contracts\Repositories\ResellerRepository;
use App\Contracts\Repositories\UserRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\FilterableResource;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\CourseAssignmentIndexResource;
use App\Http\Resources\CourseIndexResource;
use App\Http\Resources\InvoiceIndexResource;
use App\Http\Resources\QuizAttemptIndexResource;
use App\Http\Resources\ResellerKlantResource;
use App\Http\Resources\ResellerResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Maps a FilterableResource to the repository/resource pair its
 * filterable index page already uses, so GenerateExportJob can generate
 * a CSV for any of them without a giant match() of its own. Reuses each
 * index's existing JsonResource for row-shaping -- the same column
 * definitions the index page already renders, not a second copy of them.
 */
final readonly class ExportResourceRegistry
{
    /**
     * Resolves the right repository, paginates, and extracts items()
     * immediately -- all inside this one match(), rather than returning
     * a LengthAwarePaginator for the caller to unwrap. Each match arm's
     * LengthAwarePaginator<int, ItsModel> is a *different* specialization,
     * and since Laravel's LengthAwarePaginator isn't generically
     * covariant, PHPStan has no way to describe "some
     * LengthAwarePaginator<int, object>" as their common return type --
     * extracting items() (a plain array, not that generic wrapper)
     * before returning sidesteps needing one.
     *
     * @return list<object>
     */
    public function itemsFor(FilterableResource $resource, FilterRequestData $filters, int $perPage): array
    {
        return array_values(match ($resource) {
            FilterableResource::Resellers => app(ResellerRepository::class)->paginate($filters, $perPage)->items(),
            FilterableResource::Users => app(UserRepository::class)->paginate($filters, $perPage)->items(),
            FilterableResource::Klanten => app(ResellerKlantRepository::class)->paginate($filters, $perPage)->items(),
            FilterableResource::Courses => app(CourseRepository::class)->paginate($filters, $perPage)->items(),
            FilterableResource::Assignments => app(CourseAssignmentRepository::class)->paginate($filters, $perPage)->items(),
            FilterableResource::Attempts => app(QuizAttemptRepository::class)->paginate($filters, $perPage)->items(),
            FilterableResource::Activity => app(ActivityLogRepository::class)->paginate($filters, $perPage)->items(),
            FilterableResource::Invoices => app(InvoiceRepository::class)->paginate($filters, $perPage)->items(),
        });
    }

    /**
     * @return class-string<JsonResource>
     */
    public function resourceClassFor(FilterableResource $resource): string
    {
        return match ($resource) {
            FilterableResource::Resellers => ResellerResource::class,
            FilterableResource::Users => UserResource::class,
            FilterableResource::Klanten => ResellerKlantResource::class,
            FilterableResource::Courses => CourseIndexResource::class,
            FilterableResource::Assignments => CourseAssignmentIndexResource::class,
            FilterableResource::Attempts => QuizAttemptIndexResource::class,
            FilterableResource::Activity => ActivityLogResource::class,
            FilterableResource::Invoices => InvoiceIndexResource::class,
        };
    }

    /**
     * Whether this resource's repository relies on an ambient
     * TenantContext (Klanten/Courses/Assignments/Attempts/Invoices) --
     * the queued job has no HTTP request to resolve one from, so it must
     * be set explicitly from Export::reseller_id first. False for
     * platform-wide resources (Resellers/Users/Activity), which never
     * look at TenantContext at all.
     */
    public function requiresTenantContext(FilterableResource $resource): bool
    {
        return match ($resource) {
            FilterableResource::Klanten,
            FilterableResource::Courses,
            FilterableResource::Assignments,
            FilterableResource::Attempts,
            FilterableResource::Invoices => true,
            FilterableResource::Resellers,
            FilterableResource::Users,
            FilterableResource::Activity => false,
        };
    }
}
