<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Mail\CertificateIssued;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class CertificateIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Certificate $certificate) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): CertificateIssued
    {
        return (new CertificateIssued($this->certificate))->to($notifiable->email);
    }

    /**
     * @return array{type: string, certificate_id: int, verification_code: string}
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            'type' => NotificationType::Certificate->value,
            'certificate_id' => $this->certificate->id,
            'verification_code' => $this->certificate->verification_code,
        ];
    }
}
