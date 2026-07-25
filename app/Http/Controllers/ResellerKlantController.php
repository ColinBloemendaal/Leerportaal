<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResellerKlanten\CreateResellerKlant;
use App\Contracts\Repositories\ResellerKlantRepository;
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
        $search = $request->string('search')->value();

        return Inertia::render('Klanten/Index', [
            'klanten' => ResellerKlantResource::collection(
                $resellerKlanten->paginate($search !== '' ? $search : null),
            ),
        ]);
    }

    public function store(StoreResellerKlantRequest $request, CreateResellerKlant $createResellerKlant): RedirectResponse
    {
        $createResellerKlant($request->toDto());

        return to_route('klanten.index')->with('success', __('Klant created.'));
    }
}
