<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

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
}
