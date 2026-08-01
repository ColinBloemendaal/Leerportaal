<?php

declare(strict_types=1);

namespace App\Mail;

use App\Contracts\Repositories\ResellerMailTemplateRepository;
use App\Contracts\Repositories\ResellerThemeRepository;
use App\Enums\MailTemplateType;
use App\Mail\Concerns\HasResellerBranding;
use App\Models\Reseller;
use App\Models\ResellerMailTemplate;
use App\Models\ResellerTheme;
use App\Models\UserInvite;
use App\Services\Mail\MailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use RuntimeException;

final class UserInvited extends Mailable implements ShouldQueue
{
    use HasResellerBranding;
    use Queueable;
    use SerializesModels;

    private readonly Reseller $reseller;

    // Named $resellerTheme, not $theme: Mailable already declares a public
    // $theme property (the markdown mail *theme name*, e.g. "default") --
    // reusing that name would silently override its type and visibility.
    private readonly ?ResellerTheme $resellerTheme;

    private readonly ?ResellerMailTemplate $override;

    private readonly string $acceptUrl;

    public function __construct(public readonly UserInvite $invite)
    {
        $reseller = $this->invite->reseller()->first();

        if ($reseller === null) {
            throw new RuntimeException('UserInvite has no reseller (reseller_id is not nullable).');
        }

        $this->reseller = $reseller;

        // Fetched eagerly (not lazily via TenantContext-bound repository
        // methods) because this Mailable is queued: by the time envelope()
        // runs on a worker, there is no request and no ambient tenant.
        $this->resellerTheme = app(ResellerThemeRepository::class)->findForReseller($reseller->id);
        $this->override = app(ResellerMailTemplateRepository::class)
            ->findForResellerAndType($reseller->id, MailTemplateType::UserInvited);

        $this->acceptUrl = URL::temporarySignedRoute(
            'invite.accept',
            now()->addDays(7),
            [
                'reseller' => $this->reseller->slug,
                'invite' => $this->invite->id,
                'hash' => sha1($this->invite->email),
            ],
        );
    }

    public function envelope(): Envelope
    {
        $subject = app(MailTemplateRenderer::class)->renderSubject(
            $this->override,
            trans(':reseller invited you to Leerportaal', ['reseller' => $this->reseller->name]),
            $this->placeholders(),
        );

        return $this->brandedEnvelope($this->reseller, $this->resellerTheme, $subject);
    }

    public function content(): Content
    {
        if ($this->override !== null) {
            return app(MailTemplateRenderer::class)->renderOverrideContent(
                $this->override,
                $this->placeholders(),
                $this->reseller->name,
            );
        }

        return new Content(
            markdown: 'emails.invites.invited',
            with: [
                'resellerName' => $this->reseller->name,
                'inviteeName' => $this->invite->name,
                'acceptUrl' => $this->acceptUrl,
            ],
        );
    }

    /**
     * @return array<string, string>
     */
    private function placeholders(): array
    {
        return [
            'reseller_name' => $this->reseller->name,
            'invitee_name' => $this->invite->name,
            'accept_url' => $this->acceptUrl,
        ];
    }
}
