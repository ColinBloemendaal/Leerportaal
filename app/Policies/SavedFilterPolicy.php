<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SavedFilter;
use App\Models\User;

/**
 * A saved filter is scoped by user_id, not reseller -- every
 * authenticated user (platform or reseller side) may view/create their
 * own presets; only ownership matters for delete.
 */
final class SavedFilterPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, SavedFilter $savedFilter): bool
    {
        return $user->id === $savedFilter->user_id;
    }
}
