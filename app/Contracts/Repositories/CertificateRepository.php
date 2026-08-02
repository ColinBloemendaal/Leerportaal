<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Certificate;

interface CertificateRepository
{
    /**
     * For the public certificate verification page -- eager loads what
     * that page needs to render (the assignment's user and course) so
     * the controller doesn't have to.
     */
    public function findByVerificationCode(string $code): ?Certificate;
}
