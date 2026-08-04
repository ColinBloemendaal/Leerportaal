<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Quiz;

interface QuizRepository
{
    public function findById(int $id): ?Quiz;
}
