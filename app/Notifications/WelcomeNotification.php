<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Mail\Welcome as WelcomeMail;
use App\Models\User;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RespectsPreferences;

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $this->filterByPreference($notifiable, NotificationType::Welcome, ['mail', 'database']);
    }

    public function toMail(User $notifiable): WelcomeMail
    {
        // Required: when toMail() returns a Mailable instance (rather than
        // a MailMessage), Laravel's mail channel calls $message->send()
        // directly without ever addressing it -- the Mailable itself must
        // carry its own recipient.
        return (new WelcomeMail($notifiable))->to($notifiable->email);
    }

    /**
     * @return array{type: string, message: string}
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            'type' => NotificationType::Welcome->value,
            'message' => trans('Welcome to :app!', ['app' => config('app.name')]),
        ];
    }
}
