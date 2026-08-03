<?php

declare(strict_types=1);

namespace App\Mail;

use App\Contracts\Repositories\ResellerThemeRepository;
use App\DataTransferObjects\Reporting\KlantDashboardData;
use App\Mail\Concerns\HasResellerBranding;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\ResellerTheme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

/**
 * Deliberately plain -- no ResellerMailTemplate override support like
 * UserInvited has. This carries a per-cursist data table, not a short
 * placeholder-substitution transactional message, so the override
 * system's model doesn't fit; resellers get branding (sender/reply-to)
 * but not content customisation for this one.
 */
final class KlantProgressReport extends Mailable implements ShouldQueue
{
    use HasResellerBranding;
    use Queueable;
    use SerializesModels;

    private readonly Reseller $reseller;

    private readonly ?ResellerTheme $resellerTheme;

    public function __construct(
        public readonly ResellerKlant $klant,
        public readonly KlantDashboardData $report,
    ) {
        $reseller = $this->klant->reseller()->first();

        if ($reseller === null) {
            throw new RuntimeException('ResellerKlant has no reseller (reseller_id is not nullable).');
        }

        $this->reseller = $reseller;

        // Fetched eagerly, not lazily via TenantContext: this Mailable is
        // queued and runs on a worker with no ambient tenant.
        $this->resellerTheme = app(ResellerThemeRepository::class)->findForReseller($reseller->id);
    }

    public function envelope(): Envelope
    {
        return $this->brandedEnvelope(
            $this->reseller,
            $this->resellerTheme,
            trans('Weekly progress report — :klant', ['klant' => $this->klant->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reports.klant-progress',
            with: [
                'klantName' => $this->klant->name,
                'cursisten' => $this->report->cursisten,
            ],
        );
    }
}
