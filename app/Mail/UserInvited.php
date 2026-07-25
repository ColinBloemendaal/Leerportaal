<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Reseller;
use App\Models\UserInvite;
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
    use Queueable;
    use SerializesModels;

    private readonly Reseller $reseller;

    public function __construct(public readonly UserInvite $invite)
    {
        $reseller = $this->invite->reseller()->first();

        if ($reseller === null) {
            throw new RuntimeException('UserInvite has no reseller (reseller_id is not nullable).');
        }

        $this->reseller = $reseller;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans(':reseller invited you to Leerportaal', ['reseller' => $this->reseller->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invites.invited',
            with: [
                'resellerName' => $this->reseller->name,
                'inviteeName' => $this->invite->name,
                'acceptUrl' => URL::temporarySignedRoute(
                    'invite.accept',
                    now()->addDays(7),
                    [
                        'reseller' => $this->reseller->slug,
                        'invite' => $this->invite->id,
                        'hash' => sha1($this->invite->email),
                    ],
                ),
            ],
        );
    }
}
