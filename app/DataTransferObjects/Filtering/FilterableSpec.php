<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Filtering;

/**
 * What one index permits a FilterRequestData to touch -- an allowlist,
 * never derived from the request itself, so a caller can never search,
 * sort, or filter by a column the index didn't explicitly expose.
 */
final readonly class FilterableSpec
{
    /**
     * @param  list<string>  $searchableColumns
     * @param  list<string>  $allowedSorts
     * @param  list<string>  $allowedFilters
     */
    public function __construct(
        public array $searchableColumns = [],
        public array $allowedSorts = [],
        public array $allowedFilters = [],
        public ?string $defaultSort = null,
    ) {}
}
