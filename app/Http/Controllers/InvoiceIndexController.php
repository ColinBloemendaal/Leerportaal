<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\InvoiceRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Http\Resources\InvoiceIndexResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class InvoiceIndexController extends Controller
{
    public function index(Request $request, InvoiceRepository $invoices): Response
    {
        // Same access boundary as the reseller dashboard and billing dashboard.
        $this->authorize('viewAny', 'App\Models\ResellerKlant');

        $filters = FilterRequestData::fromRequest($request);

        return Inertia::render('Admin/Reseller/Invoices/Index', [
            'invoices' => InvoiceIndexResource::collection($invoices->paginate($filters)),
            'query' => [
                'search' => $filters->search,
                'sort' => $filters->sort,
                'direction' => $filters->sortDirection,
                'filters' => $filters->filters,
            ],
        ]);
    }
}
