<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Reads over Laravel's own notifications table (Illuminate\Notifications\DatabaseNotification,
 * not an App\Models class -- there is nothing app-specific to model,
 * Notifiable already owns the relationship). Scoped by user id, not a
 * User model, matching ExportRepository's own established convention so
 * controllers never need to import App\Models\User directly.
 */
interface NotificationRepository
{
    /**
     * @return LengthAwarePaginator<int, DatabaseNotification>
     */
    public function forUser(int $userId, int $perPage = 20): LengthAwarePaginator;

    public function unreadCountForUser(int $userId): int;

    public function findOwnById(int $userId, string $id): ?DatabaseNotification;

    /**
     * Every notification created after the given moment (or ever, if
     * null) -- the digest command's source of what's accumulated since
     * the user's last digest send. Reuses the same database rows the
     * mail channel's sibling send already created, rather than a
     * separate "pending digest item" table.
     *
     * @return Collection<int, DatabaseNotification>
     */
    public function createdSince(int $userId, ?CarbonInterface $since): Collection;
}
