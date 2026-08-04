<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Collection;

interface CertificateRepository
{
    /**
     * For the public certificate verification page -- eager loads what
     * that page needs to render (the assignment's user and course) so
     * the controller doesn't have to.
     */
    public function findByVerificationCode(string $code): ?Certificate;

    /**
     * Every certificate this user has earned -- the GDPR data-subject
     * export's source (a user's own data export must never depend on the
     * request's ambient tenant).
     *
     * @return Collection<int, Certificate>
     */
    public function forUser(int $userId): Collection;
}
