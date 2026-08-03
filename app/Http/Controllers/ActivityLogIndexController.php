<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\ActivityLogRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ActivityLogIndexController extends Controller
{
    public function index(Request $request, ActivityLogRepository $activity): Response
    {
        // Same access boundary as the other platform-wide indexes.
        $this->authorize('viewAny', 'App\Models\Reseller');

        $filters = FilterRequestData::fromRequest($request);

        return Inertia::render('Admin/Platform/Activity/Index', [
            'activity' => ActivityLogResource::collection($activity->paginate($filters)),
            'query' => [
                'search' => $filters->search,
                'sort' => $filters->sort,
                'direction' => $filters->sortDirection,
                'filters' => $filters->filters,
            ],
        ]);
    }
}
