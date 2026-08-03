<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\NotificationRepository;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentNotificationRepository implements NotificationRepository
{
    public function forUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->baseQuery($userId)->orderByDesc('created_at')->paginate($perPage);
    }

    public function unreadCountForUser(int $userId): int
    {
        return $this->baseQuery($userId)->whereNull('read_at')->count();
    }

    public function findOwnById(int $userId, string $id): ?DatabaseNotification
    {
        return $this->baseQuery($userId)->whereKey($id)->first();
    }

    public function createdSince(int $userId, ?CarbonInterface $since): Collection
    {
        $query = $this->baseQuery($userId)->orderBy('created_at');

        if ($since !== null) {
            $query->where('created_at', '>', $since);
        }

        return $query->get();
    }

    /**
     * @return Builder<DatabaseNotification>
     */
    private function baseQuery(int $userId): Builder
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId);
    }
}
