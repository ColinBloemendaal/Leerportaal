<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResellerKlanten\CreateResellerKlant;
use App\Actions\ResellerKlanten\DeleteResellerKlant;
use App\Actions\ResellerKlanten\RestoreResellerKlant;
use App\Contracts\Repositories\ResellerKlantRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Http\Requests\ResellerKlanten\StoreResellerKlantRequest;
use App\Http\Resources\ResellerKlantResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ResellerKlantController extends Controller
{
    public function index(Request $request, ResellerKlantRepository $resellerKlanten): Response
    {
        $filters = FilterRequestData::fromRequest($request);

        return Inertia::render('Klanten/Index', [
            'klanten' => ResellerKlantResource::collection($resellerKlanten->paginate($filters)),
            'trashed' => ResellerKlantResource::collection($resellerKlanten->trashed()),
            'query' => [
                'search' => $filters->search,
                'sort' => $filters->sort,
                'direction' => $filters->sortDirection,
                'filters' => $filters->filters,
            ],
        ]);
    }

    public function store(StoreResellerKlantRequest $request, CreateResellerKlant $createResellerKlant): RedirectResponse
    {
        $createResellerKlant($request->toDto());

        return to_route('klanten.index')->with('success', __('Klant created.'));
    }

    public function destroy(int $klant, ResellerKlantRepository $resellerKlanten, DeleteResellerKlant $deleteResellerKlant): RedirectResponse
    {
        $resellerKlant = $resellerKlanten->findById($klant);

        abort_if($resellerKlant === null, 404);

        $this->authorize('delete', $resellerKlant);

        $deleteResellerKlant($resellerKlant);

        return to_route('klanten.index')->with('success', __('Klant deleted.'));
    }

    public function restore(int $klant, ResellerKlantRepository $resellerKlanten, RestoreResellerKlant $restoreResellerKlant): RedirectResponse
    {
        $resellerKlant = $resellerKlanten->findTrashedById($klant);

        abort_if($resellerKlant === null, 404);

        $this->authorize('restore', $resellerKlant);

        $restoreResellerKlant($resellerKlant);

        return to_route('klanten.index')->with('success', __('Klant restored.'));
    }
}
