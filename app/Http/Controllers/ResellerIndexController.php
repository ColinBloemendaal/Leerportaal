<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\ResellerRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Http\Resources\ResellerResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ResellerIndexController extends Controller
{
    public function index(Request $request, ResellerRepository $resellers): Response
    {
        $this->authorize('viewAny', 'App\Models\Reseller');

        $filters = FilterRequestData::fromRequest($request);

        return Inertia::render('Admin/Platform/Resellers/Index', [
            'resellers' => ResellerResource::collection($resellers->paginate($filters)),
            'query' => [
                'search' => $filters->search,
                'sort' => $filters->sort,
                'direction' => $filters->sortDirection,
                'filters' => $filters->filters,
            ],
        ]);
    }
}
