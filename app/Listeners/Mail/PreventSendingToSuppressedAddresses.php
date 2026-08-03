<?php

declare(strict_types=1);

namespace App\Listeners\Mail;

use App\Contracts\Repositories\SuppressedEmailRepository;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;

/**
 * One global hook for the whole app rather than a per-Mailable check:
 * returning false from a Illuminate\Mail\Events\MessageSending listener
 * aborts the send entirely (Illuminate\Mail\Mailer::send()). Deliberately
 * NOT queued (ShouldQueue): it must run synchronously, in the same process
 * as the send it's allowed to cancel -- queuing it would let the original
 * mail go out before this ever ran.
 *
 * Every notification mailable in this codebase addresses exactly one
 * recipient (see App\Mail\Concerns\HasResellerBranding's callers), so
 * aborting the whole message on any suppressed address is equivalent to
 * per-recipient filtering here -- there's no multi-recipient mailable that
 * would need a partial-send instead.
 */
final class PreventSendingToSuppressedAddresses
{
    public function __construct(private readonly SuppressedEmailRepository $suppressedEmails) {}

    public function handle(MessageSending $event): bool
    {
        $addresses = [
            ...$event->message->getTo(),
            ...$event->message->getCc(),
            ...$event->message->getBcc(),
        ];

        foreach ($addresses as $address) {
            if ($this->suppressedEmails->isSuppressed($address->getAddress())) {
                // CLAUDE.md §7: never log PII. The address itself never
                // appears -- see suppressed_emails for the actual record.
                Log::info('Skipped sending mail to a suppressed address.');

                return false;
            }
        }

        return true;
    }
}
