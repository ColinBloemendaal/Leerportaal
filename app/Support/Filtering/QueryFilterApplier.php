<?php

declare(strict_types=1);

namespace App\Support\Filtering;

use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Database\Eloquent\Builder;

/**
 * Applies a FilterRequestData to an Eloquent builder, constrained to
 * whatever a FilterableSpec allowlists. Lives outside the
 * Actions/Repositories/Services layering -- it's a generic, stateless
 * query helper, not a domain read (Repositories still own the actual
 * query construction and still never leak a Builder to their callers;
 * this only helps them build one).
 *
 * Generic per-call (via the method's own @template), not per-instance --
 * one shared applier is used across every model, unlike a repository
 * which is bound to a single model.
 */
final readonly class QueryFilterApplier
{
    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query, FilterRequestData $filters, FilterableSpec $spec): Builder
    {
        if ($filters->search !== null && $spec->searchableColumns !== []) {
            $query->where(function (Builder|QueryBuilderContract $inner) use ($filters, $spec): void {
                foreach ($spec->searchableColumns as $column) {
                    $inner->orWhere($column, 'like', '%'.$filters->search.'%');
                }
            });
        }

        foreach ($filters->filters as $field => $value) {
            if (in_array($field, $spec->allowedFilters, true)) {
                $query->where($field, $value);
            }
        }

        $sort = in_array($filters->sort, $spec->allowedSorts, true) ? $filters->sort : $spec->defaultSort;

        if ($sort !== null) {
            $query->orderBy($sort, $filters->sortDirection);
        }

        return $query;
    }
}
