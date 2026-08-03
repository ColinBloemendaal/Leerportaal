<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\QuizAttempt;
use Illuminate\Pagination\LengthAwarePaginator;

interface QuizAttemptRepository
{
    /**
     * QuizAttempt has no reseller_id of its own (see App\Models\QuizAttempt)
     * -- scoped to the current reseller via the attempting user's own
     * reseller_id instead.
     *
     * @return LengthAwarePaginator<int, QuizAttempt>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator;
}
