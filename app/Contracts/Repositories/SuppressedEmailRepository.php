<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

interface SuppressedEmailRepository
{
    public function isSuppressed(string $email): bool;
}
