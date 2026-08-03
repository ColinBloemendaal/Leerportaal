<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Enums\ResellerStatus;
use App\Enums\Role;
use App\Models\Invoice;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\AccountSuspendedNotification;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

/**
 * The last resort of dunning: called once RetryOverdueInvoicePayment has
 * exhausted its retry limit for a given invoice and it's still Overdue.
 * Idempotent -- does nothing if the reseller is already suspended, so a
 * second overdue invoice for the same already-suspended reseller is a
 * silent no-op rather than a duplicate notification.
 */
final readonly class SuspendResellerForNonPayment
{
    public function __construct(private PermissionRegistrar $permissions) {}

    public function __invoke(Invoice $invoice): bool
    {
        $reseller = $invoice->reseller()->first();

        if ($reseller === null || $reseller->status === ResellerStatus::Suspended) {
            return false;
        }

        $reseller->status = ResellerStatus::Suspended;
        $reseller->save();

        foreach ($this->resellerAdmins($reseller) as $admin) {
            $admin->notify(new AccountSuspendedNotification($reseller));
        }

        return true;
    }

    /**
     * @return Collection<int, User>
     */
    private function resellerAdmins(Reseller $reseller): Collection
    {
        // hasRole() checks against the ambient team id -- set it
        // explicitly since this runs from a scheduled command with no
        // per-request tenant resolution, same pattern as
        // SendKlantProgressReport::klantAdmins().
        $this->permissions->setPermissionsTeamId($reseller->id);

        return User::query()
            ->where('reseller_id', $reseller->id)
            ->get()
            ->filter(fn (User $user): bool => $user->hasRole(Role::ResellerAdmin->value))
            ->values();
    }
}
