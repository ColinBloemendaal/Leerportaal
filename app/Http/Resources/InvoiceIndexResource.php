<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\InvoiceStatus;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $period_start
 * @property Carbon $period_end
 * @property InvoiceStatus $status
 * @property ?Money $subtotal_cents
 * @property ?Money $vat_cents
 * @property bool $reverse_charge
 * @property ?Money $total_cents
 * @property ?Carbon $issued_at
 * @property ?Carbon $paid_at
 */
final class InvoiceIndexResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_start' => $this->period_start->toDateString(),
            'period_end' => $this->period_end->toDateString(),
            'status' => $this->status->value,
            'subtotal_cents' => $this->subtotal_cents === null ? 0 : $this->subtotal_cents->cents,
            'vat_cents' => $this->vat_cents === null ? 0 : $this->vat_cents->cents,
            'reverse_charge' => $this->reverse_charge,
            'total_cents' => $this->total_cents === null ? 0 : $this->total_cents->cents,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
        ];
    }
}
