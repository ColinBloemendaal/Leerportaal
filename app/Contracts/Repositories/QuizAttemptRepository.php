<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends FilterablePaginator<QuizAttempt>
 */
interface QuizAttemptRepository extends FilterablePaginator
{
    /**
     * QuizAttempt has no reseller_id of its own (see App\Models\QuizAttempt)
     * -- scoped to the current reseller via the attempting user's own
     * reseller_id instead.
     *
     * @return LengthAwarePaginator<int, QuizAttempt>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Every attempt this user has ever made, regardless of reseller
     * context -- the GDPR data-subject export's source (a user's own
     * data export must never depend on the request's ambient tenant).
     *
     * @return Collection<int, QuizAttempt>
     */
    public function forUser(int $userId): Collection;

    /**
     * For QuizAttemptController -- no additional scoping here, since the
     * controller's own policy check is what decides whether the acting
     * user may act on whatever this returns.
     */
    public function findById(int $id): ?QuizAttempt;

    /**
     * The most recent submitted attempt for one quiz+user -- shown when
     * StartQuizAttempt refuses a new attempt (limit reached / cooldown
     * active).
     */
    public function latestSubmittedForQuizAndUser(int $quizId, int $userId): ?QuizAttempt;
}
