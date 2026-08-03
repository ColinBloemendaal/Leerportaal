<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\UserRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class UserIndexController extends Controller
{
    public function index(Request $request, UserRepository $users): Response
    {
        // Same access boundary as the platform dashboard/resellers index --
        // this is a platform-wide view across every reseller, not a
        // reseller-scoped one, so UserPolicy (which is reseller-scoped) is
        // deliberately not used here.
        $this->authorize('viewAny', 'App\Models\Reseller');

        $filters = FilterRequestData::fromRequest($request);

        return Inertia::render('Admin/Platform/Users/Index', [
            'users' => UserResource::collection($users->paginate($filters)),
            'query' => [
                'search' => $filters->search,
                'sort' => $filters->sort,
                'direction' => $filters->sortDirection,
                'filters' => $filters->filters,
            ],
        ]);
    }
}
