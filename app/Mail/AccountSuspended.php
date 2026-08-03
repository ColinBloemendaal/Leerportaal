<?php

declare(strict_types=1);

namespace App\Mail;

use App\Contracts\Repositories\ResellerThemeRepository;
use App\Mail\Concerns\HasResellerBranding;
use App\Models\Reseller;
use App\Models\ResellerTheme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AccountSuspended extends Mailable implements ShouldQueue
{
    use HasResellerBranding;
    use Queueable;
    use SerializesModels;

    private readonly ?ResellerTheme $resellerTheme;

    public function __construct(public readonly Reseller $reseller)
    {
        $this->theme = 'reseller';

        // Fetched eagerly, not lazily via TenantContext: this Mailable is
        // queued and runs on a worker with no ambient tenant.
        $this->resellerTheme = app(ResellerThemeRepository::class)->findForReseller($reseller->id);
    }

    public function envelope(): Envelope
    {
        return $this->brandedEnvelope(
            $this->reseller,
            $this->resellerTheme,
            trans('Your account has been suspended'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.billing.account-suspended',
            with: $this->brandingData($this->reseller, $this->resellerTheme),
        );
    }
}
