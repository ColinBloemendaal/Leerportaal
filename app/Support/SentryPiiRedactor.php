<?php

declare(strict_types=1);

namespace App\Support;

use Sentry\Event;

/**
 * Registered as Sentry's `before_send` callback (config/sentry.php).
 * Defense-in-depth on top of `send_default_pii => false` -- see
 * CLAUDE.md §7 (PII must never reach Sentry).
 */
final class SentryPiiRedactor
{
    /**
     * @var array<int, string>
     */
    private const PII_FIELDS = [
        'password', 'password_confirmation', 'email', 'name', 'first_name', 'last_name',
        'phone', 'telefoon', 'address', 'adres', 'iban', 'bsn', 'token', 'authorization', 'cookie',
    ];

    public static function handle(Event $event): Event
    {
        $event->setUser(null);

        $request = $event->getRequest();
        if ($request !== []) {
            $event->setRequest(self::redact($request));
        }

        $extra = $event->getExtra();
        if ($extra !== []) {
            $event->setExtra(self::redact($extra));
        }

        return $event;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    private static function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::redact($value);

                continue;
            }

            if (in_array(strtolower((string) $key), self::PII_FIELDS, true)) {
                $data[$key] = '[redacted]';
            }
        }

        return $data;
    }
}
