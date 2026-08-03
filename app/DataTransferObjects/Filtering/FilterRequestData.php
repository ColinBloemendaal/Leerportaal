<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Filtering;

use Illuminate\Http\Request;

/**
 * Parsed once from the request's query string, then applied by
 * QueryFilterApplier against whatever columns a given index allows --
 * see TODO.md's "generic filter/sort/search layer reusable across every
 * index" task. Centralizes the ?search=&sort=&direction=&filter[x]=y
 * query-string shape so every index controller doesn't reinvent it.
 */
final readonly class FilterRequestData
{
    /**
     * @param  array<string, string>  $filters
     */
    public function __construct(
        public ?string $search,
        public ?string $sort,
        public string $sortDirection,
        public array $filters,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $search = $request->string('search')->value();
        $direction = $request->string('direction')->lower()->value();

        /** @var array<string, mixed> $filters */
        $filters = (array) $request->input('filter', []);

        return new self(
            search: $search === '' ? null : $search,
            sort: $request->string('sort')->value() ?: null,
            sortDirection: $direction === 'desc' ? 'desc' : 'asc',
            filters: array_filter($filters, static fn (mixed $value): bool => is_string($value) && $value !== ''),
        );
    }

    /**
     * Reconstructs from Export::$filters (the same shape this class's
     * own properties serialize to as JSON) -- GenerateExportJob has no
     * HTTP request to parse, only what was captured when the export was
     * requested.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, string> $filters */
        $filters = is_array($data['filters'] ?? null) ? $data['filters'] : [];

        return new self(
            search: isset($data['search']) && is_string($data['search']) ? $data['search'] : null,
            sort: isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : null,
            sortDirection: ($data['sortDirection'] ?? null) === 'desc' ? 'desc' : 'asc',
            filters: $filters,
        );
    }
}
