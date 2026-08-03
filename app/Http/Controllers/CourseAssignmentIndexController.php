<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\CourseAssignmentRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Http\Resources\CourseAssignmentIndexResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CourseAssignmentIndexController extends Controller
{
    public function index(Request $request, CourseAssignmentRepository $assignments): Response
    {
        // Same access boundary as the reseller dashboard.
        $this->authorize('viewAny', 'App\Models\ResellerKlant');

        $filters = FilterRequestData::fromRequest($request);

        return Inertia::render('Admin/Reseller/Assignments/Index', [
            'assignments' => CourseAssignmentIndexResource::collection($assignments->paginate($filters)),
            'query' => [
                'search' => $filters->search,
                'sort' => $filters->sort,
                'direction' => $filters->sortDirection,
                'filters' => $filters->filters,
            ],
        ]);
    }
}
