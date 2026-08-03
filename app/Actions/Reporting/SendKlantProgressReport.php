<?php

declare(strict_types=1);

namespace App\Actions\Reporting;

use App\Mail\KlantProgressReport;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Services\Reporting\KlantDashboardService;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

final readonly class SendKlantProgressReport
{
    public function __construct(
        private KlantDashboardService $dashboard,
        private Mailer $mailer,
    ) {}

    /**
     * Returns the number of klant-admins the report was sent to.
     */
    public function __invoke(ResellerKlant $klant): int
    {
        $admins = $this->klantAdmins($klant);

        if ($admins->isEmpty()) {
            return 0;
        }

        $report = $this->dashboard->forKlant($klant);

        foreach ($admins as $admin) {
            $this->mailer->to($admin->email)->send(new KlantProgressReport($klant, $report));
        }

        return $admins->count();
    }

    /**
     * @return Collection<int, User>
     */
    private function klantAdmins(ResellerKlant $klant): Collection
    {
        // hasRole() checks against the ambient team id -- set it explicitly
        // since this runs from a scheduled command with no per-request
        // tenant resolution, matching the pattern in UserPolicy::impersonate().
        app(PermissionRegistrar::class)->setPermissionsTeamId($klant->reseller_id);

        return $klant->cursisten()->get()
            ->filter(fn (User $user): bool => $user->hasRole('klant-admin'))
            ->values();
    }
}
