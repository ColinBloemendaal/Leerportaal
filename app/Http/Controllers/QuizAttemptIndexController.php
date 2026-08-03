<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\QuizAttemptRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Http\Resources\QuizAttemptIndexResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class QuizAttemptIndexController extends Controller
{
    public function index(Request $request, QuizAttemptRepository $attempts): Response
    {
        // Same access boundary as the reseller dashboard.
        $this->authorize('viewAny', 'App\Models\ResellerKlant');

        $filters = FilterRequestData::fromRequest($request);

        return Inertia::render('Admin/Reseller/Attempts/Index', [
            'attempts' => QuizAttemptIndexResource::collection($attempts->paginate($filters)),
            'query' => [
                'search' => $filters->search,
                'sort' => $filters->sort,
                'direction' => $filters->sortDirection,
                'filters' => $filters->filters,
            ],
        ]);
    }
}
