<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Contracts\Repositories\NotificationRepository;
use App\Mail\NotificationDigest;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailer;

final readonly class SendNotificationDigest
{
    public function __construct(
        private NotificationRepository $notifications,
        private Mailer $mailer,
    ) {}

    /**
     * Returns true if a digest was actually sent (nothing to report is
     * not an error, just a no-op).
     */
    public function __invoke(User $user): bool
    {
        $items = $this->notifications->createdSince($user->id, $user->notification_digest_sent_at);

        /** @var list<string> $messages */
        $messages = $items
            ->map(fn ($notification): ?string => is_string($notification->data['message'] ?? null) ? $notification->data['message'] : null)
            ->filter()
            ->values()
            ->all();

        if ($messages === []) {
            return false;
        }

        $this->mailer->to($user->email)->send(new NotificationDigest($user, $messages));

        $user->forceFill(['notification_digest_sent_at' => now()])->save();

        return true;
    }
}
