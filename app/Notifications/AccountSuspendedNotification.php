<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Mail\AccountSuspended;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Deliberately does NOT use RespectsPreferences, same reasoning as
 * AdminAlertNotification: being suspended for non-payment isn't something
 * a reseller admin should be able to silence for themselves.
 */
final class AccountSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Reseller $reseller) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): AccountSuspended
    {
        return (new AccountSuspended($this->reseller))->to($notifiable->email);
    }

    /**
     * @return array{type: string, message: string}
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            'type' => NotificationType::Billing->value,
            'message' => trans('Your account with :reseller has been suspended due to a failed payment.', [
                'reseller' => $this->reseller->name,
            ]),
        ];
    }
}
