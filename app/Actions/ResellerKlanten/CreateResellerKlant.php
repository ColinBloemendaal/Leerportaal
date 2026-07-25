<?php

declare(strict_types=1);

namespace App\Actions\ResellerKlanten;

use App\DataTransferObjects\ResellerKlanten\CreateResellerKlantData;
use App\Models\ResellerKlant;

final readonly class CreateResellerKlant
{
    public function __invoke(CreateResellerKlantData $data): ResellerKlant
    {
        return ResellerKlant::create([
            'name' => $data->name,
        ]);
    }
}
