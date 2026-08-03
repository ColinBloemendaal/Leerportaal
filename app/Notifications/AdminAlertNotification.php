<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The one notification type with no reseller in the picture at all --
 * recipients are platform super-admins, not a reseller's own staff or
 * cursisten. Uses Notification's own MailMessage builder rather than a
 * App\Mail\* class + HasResellerBranding, unlike every other type in
 * this catalogue: there's no reseller to brand the email as being from.
 */
final class AdminAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $subject,
        private readonly string $message,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->line($this->message);
    }

    /**
     * @return array{type: string, subject: string, message: string}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => NotificationType::AdminAlert->value,
            'subject' => $this->subject,
            'message' => $this->message,
        ];
    }
}
