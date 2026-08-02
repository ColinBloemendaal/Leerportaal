<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

interface QuestionRepository
{
    /**
     * Every platform bank question (reseller_id null) plus the current
     * reseller's own -- never another reseller's. Question has no
     * TenantScope (a row can be platform- or reseller-owned, a shape the
     * strict global scope doesn't support), so visibility is composed
     * here explicitly instead.
     *
     * @return Collection<int, Question>
     */
    public function visibleToCurrentReseller(): Collection;
}
