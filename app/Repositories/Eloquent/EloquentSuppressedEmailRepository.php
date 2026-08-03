<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SuppressedEmailRepository;
use App\Models\SuppressedEmail;

final class EloquentSuppressedEmailRepository implements SuppressedEmailRepository
{
    public function isSuppressed(string $email): bool
    {
        return SuppressedEmail::query()->where('email', $email)->exists();
    }
}
