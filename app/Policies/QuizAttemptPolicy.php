<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\QuizAttempt;
use App\Models\User;

final class QuizAttemptPolicy
{
    public function submit(User $user, QuizAttempt $attempt): bool
    {
        return $user->id === $attempt->user_id;
    }
}
