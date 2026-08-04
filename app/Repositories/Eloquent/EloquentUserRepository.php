<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\DigestFrequency;
use App\Enums\Role;
use App\Models\User;
use App\Support\Filtering\QueryFilterApplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

final class EloquentUserRepository implements UserRepository
{
    public function __construct(private readonly QueryFilterApplier $filters) {}

    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $spec = new FilterableSpec(
            searchableColumns: ['name', 'email'],
            allowedSorts: ['name', 'email', 'created_at'],
            allowedFilters: ['reseller_id', 'platform_role'],
            defaultSort: 'name',
        );

        return $this->filters->apply(User::query()->with('reseller'), $filters, $spec)->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return User::query()->with('reseller')->find($id);
    }

    public function superAdmins(): Collection
    {
        return User::query()->where('platform_role', Role::SuperAdmin)->get();
    }

    public function withDigestFrequency(DigestFrequency $frequency): LazyCollection
    {
        return User::query()->where('notification_digest_frequency', $frequency)->cursor();
    }

    public function cursistenForReseller(int $resellerId): Collection
    {
        return User::query()
            ->where('reseller_id', $resellerId)
            ->whereNotNull('resellerklant_id')
            ->orderBy('name')
            ->get();
    }
}
