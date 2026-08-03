<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\CourseRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Http\Resources\CourseIndexResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CourseIndexController extends Controller
{
    public function index(Request $request, CourseRepository $courses): Response
    {
        // Same access boundary as the reseller dashboard -- any
        // reseller-side user may browse the catalog plus their own
        // authored courses.
        $this->authorize('viewAny', 'App\Models\ResellerKlant');

        $filters = FilterRequestData::fromRequest($request);

        return Inertia::render('Admin/Reseller/Courses/Index', [
            'courses' => CourseIndexResource::collection($courses->paginate($filters)),
            'query' => [
                'search' => $filters->search,
                'sort' => $filters->sort,
                'direction' => $filters->sortDirection,
                'filters' => $filters->filters,
            ],
        ]);
    }
}
