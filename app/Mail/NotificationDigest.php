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

final class NotificationDigest extends Mailable implements ShouldQueue
{
    use HasResellerBranding;
    use Queueable;
    use SerializesModels;

    private readonly ?Reseller $reseller;

    private readonly ?ResellerTheme $resellerTheme;

    /**
     * @param  list<string>  $messages
     */
    public function __construct(
        public readonly User $user,
        public readonly array $messages,
    ) {
        $reseller = $this->user->reseller()->first();

        // Platform staff have no reseller and get the platform's own
        // default branding (brandedEnvelope already handles a null
        // reseller/theme pair the same way UserInvited etc. never need
        // to, since every other digest recipient always has one) --
        // unlike those, a digest can legitimately go to platform staff
        // (e.g. an admin who subscribed to AdminAlert digests), so this
        // one doesn't throw when reseller is null.
        if ($reseller === null && $this->user->reseller_id !== null) {
            throw new RuntimeException("User #{$this->user->id} has a reseller_id but no matching Reseller row.");
        }

        $this->reseller = $reseller;
        $this->resellerTheme = $reseller === null ? null : app(ResellerThemeRepository::class)->findForReseller($reseller->id);
    }

    public function envelope(): Envelope
    {
        if ($this->reseller === null) {
            return new Envelope(subject: trans('Your notification digest'));
        }

        return $this->brandedEnvelope($this->reseller, $this->resellerTheme, trans('Your notification digest'));
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.notifications.digest',
            with: ['messages' => $this->messages],
        );
    }
}
