<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\QuizRepository;
use App\Models\Quiz;

final class EloquentQuizRepository implements QuizRepository
{
    public function findById(int $id): ?Quiz
    {
        return Quiz::query()->find($id);
    }
}
