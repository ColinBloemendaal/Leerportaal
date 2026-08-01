<?php

declare(strict_types=1);

namespace App\Actions\Theming;

use App\DataTransferObjects\Theming\UpdateResellerThemeData;
use App\Models\ResellerTheme;
use App\Tenancy\TenantContext;

final readonly class UpdateResellerTheme
{
    public function __construct(private TenantContext $tenantContext) {}

    public function __invoke(UpdateResellerThemeData $data): ResellerTheme
    {
        return ResellerTheme::updateOrCreate(
            ['reseller_id' => $this->tenantContext->id()],
            [
                'primary_color' => $data->primaryColor,
                'secondary_color' => $data->secondaryColor,
                'accent_color' => $data->accentColor,
                'font_family' => $data->fontFamily,
            ],
        );
    }
}
