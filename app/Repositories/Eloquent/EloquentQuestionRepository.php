<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\QuestionRepository;
use App\Models\Question;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Collection;

final class EloquentQuestionRepository implements QuestionRepository
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function visibleToCurrentReseller(): Collection
    {
        $tenantId = $this->tenantContext->id();

        return Question::query()
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('reseller_id')->orWhere('reseller_id', $tenantId);
            })
            ->get();
    }
}
