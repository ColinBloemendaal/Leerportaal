<?php

declare(strict_types=1);

namespace App\Actions\ResellerKlanten;

use App\Models\ResellerKlant;

final readonly class RestoreResellerKlant
{
    public function __invoke(ResellerKlant $klant): void
    {
        $klant->restore();
    }
}
