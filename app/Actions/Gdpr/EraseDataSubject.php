<?php

declare(strict_types=1);

namespace App\Actions\Gdpr;

use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;

/**
 * CLAUDE.md §8 (GDPR): "Erasure with anonymization strategy (preserve
 * invoices/audit as legally required)." Scrubs the personal-data columns
 * on the user's own row and soft-deletes the account; everything that
 * merely *references* the user (invoices, invoice lines, credit notes,
 * course assignments, quiz attempts, certificates, activity log entries)
 * is left completely untouched -- none of those tables store PII of
 * their own, only a `user_id`/`causer_id` foreign key, so the erasure
 * this codebase owes is entirely contained in this one row. Invoices
 * stay billable history and the activity log stays a true audit trail,
 * exactly as CLAUDE.md requires.
 *
 * Idempotent: a second request against an already-erased user is a
 * no-op, so re-processing the same data-subject request twice (or a
 * stale UI retry) never re-anonymizes an already-blank row.
 *
 * Known, deliberate gap: an issued certificate's PDF file (on disk, not
 * in this table) has the recipient's name rendered into its content at
 * issuance time. Regenerating/redacting that PDF is a materially larger
 * feature (there's no existing PDF-regeneration Action to reuse) than
 * this task's "anonymization strategy" scope, so it's left as a known
 * follow-up rather than silently ignored or built speculatively here.
 */
final readonly class EraseDataSubject
{
    public function __construct(private ConnectionInterface $db) {}

    public function __invoke(User $user): User
    {
        if ($user->isErased()) {
            return $user;
        }

        return $this->db->transaction(function () use ($user): User {
            $user->name = 'Erased user';
            $user->email = sprintf('erased-user-%d@erased.invalid', $user->id);
            $user->password = Str::random(64);
            $user->remember_token = Str::random(60);
            $user->two_factor_secret = null;
            $user->two_factor_recovery_codes = null;
            $user->two_factor_confirmed_at = null;
            $user->erased_at = now()->toImmutable();
            $user->save();

            $user->delete();

            // Nothing should still be logged in as this account once
            // it's been erased.
            $this->db->table('sessions')->where('user_id', $user->id)->delete();

            return $user;
        });
    }
}
