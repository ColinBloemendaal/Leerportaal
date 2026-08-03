<?php

declare(strict_types=1);

namespace App\Mail;

use App\Contracts\Repositories\ResellerThemeRepository;
use App\Mail\Concerns\HasResellerBranding;
use App\Models\Reseller;
use App\Models\ResellerTheme;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

/**
 * Deliberately plain, same call as KlantProgressReport -- a one-line
 * welcome message has nothing worth routing through
 * MailTemplateRenderer's placeholder-override system.
 */
final class Welcome extends Mailable implements ShouldQueue
{
    use HasResellerBranding;
    use Queueable;
    use SerializesModels;

    private readonly Reseller $reseller;

    private readonly ?ResellerTheme $resellerTheme;

    public function __construct(public readonly User $user)
    {
        // Untyped assignment, not a re-declared property: Mailable's own
        // $theme is intentionally untyped, and Larastan flags overriding it
        // with a native type (property.extraNativeType).
        $this->theme = 'reseller';

        $reseller = $this->user->reseller()->first();

        if ($reseller === null) {
            throw new RuntimeException("User #{$this->user->id} has no reseller; Welcome only fires for invite-accepted accounts.");
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
            trans('Welcome to :reseller', ['reseller' => $this->reseller->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome',
            with: [
                'userName' => $this->user->name,
                ...$this->brandingData($this->reseller, $this->resellerTheme),
            ],
        );
    }
}
