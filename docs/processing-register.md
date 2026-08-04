---
version: 2026-08-04
---

# Record of Processing Activities

Maintained per Article 30 GDPR. Reflects what this codebase actually does, not an aspirational description -- update this file in the same commit as any change to the data model, retention policy, or sub-processor list it describes.

## 1. Controller and processor roles

The platform operator is the **processor**. Each reseller is the **controller** for its own klanten and cursisten's personal data -- see [`docs/dpa.md`](dpa.md) for the contractual terms this reflects. The platform operator is the controller only for its own platform staff accounts.

## 2. Categories of data subjects

- Platform staff (`users.reseller_id IS NULL`).
- Reseller staff/admins (`users.reseller_id` set, no `resellerklant_id`).
- Klanten (customer organizations) and their own staff.
- Cursisten (end learners), each belonging to exactly one klant -- see CLAUDE.md §1, "no transfer feature."

## 3. Categories of personal data

| Category | Where it lives | Notes |
| --- | --- | --- |
| Identity (name, email) | `users` table | The only PII columns on the identity record itself -- see `App\Actions\Gdpr\EraseDataSubject`'s own docblock. |
| Course progress and completion | `course_assignments`, `quiz_attempts` | References `user_id`; no personal data of its own beyond the answers/scores tied to that user. |
| Certificates | `certificates` | Verification code + generated PDF (the PDF has the recipient's name rendered into its content -- see the erasure Action's documented gap). |
| Billing | `invoices`, `invoice_lines`, `credit_notes` | Never anonymized or deleted, per CLAUDE.md §11 (immutable once issued) and the erasure Action's own scope decision. |
| Notifications | `notifications` (Laravel's own table) | Subject to the retention policy in Section 7. |
| Audit trail | `activity_log` (spatie/laravel-activitylog) | Subject to the platform-wide (not reseller-configurable) retention policy in Section 7. |

## 4. Purposes of processing

Delivering e-learning courses, grading assessments, issuing certificates, billing resellers for course assignments (CLAUDE.md §11), and platform notifications (deadline reminders, digest emails, billing/dunning notices).

## 5. Categories of recipients

No personal data is shared outside the sub-processors listed in [`docs/subprocessors.md`](subprocessors.md). Resellers see only their own klanten/cursisten's data (enforced by `TenantScope`, see CLAUDE.md §2); platform staff access is logged via the audit trail and, for impersonation specifically, via the dedicated `impersonations` table.

## 6. International transfers

See [`docs/subprocessors.md`](subprocessors.md) for each sub-processor's own location and any transfer safeguards (standard contractual clauses, adequacy decisions) their own DPA provides.

## 7. Retention periods

Enforced by `App\Console\Commands\EnforceRetentionPoliciesCommand`, daily. Defaults in `config/gdpr.php`, a reseller may shorten (never lengthen) notifications and export retention via its own `settings->retention` -- see `App\Services\Gdpr\RetentionPolicy`. Invoices and certificates are retained indefinitely (billing/legal record and proof of completion respectively); this is a deliberate scope decision documented in `TODO.md`'s own retention-policy task, not an oversight.

## 8. Data subject rights

- **Access/portability**: `Settings > My data` (JSON and human-readable export), `App\Services\Gdpr\DataSubjectExportService`.
- **Erasure**: platform staff or the reseller's own admin, from the platform user detail page, `App\Actions\Gdpr\EraseDataSubject`.
- **Rectification**: via the reseller's own user-management features (name/email edits) -- no separate mechanism needed beyond normal account editing.

## 9. Security measures

Tenant-level data isolation (`TenantScope`, fails closed with no ambient tenant), 2FA (mandatory for platform roles), audit logging on every model touching user data/course content/assignments/billing/permissions, PII redaction before error reporting (Sentry), encrypted-at-rest 2FA secrets, private-by-default file storage with content-type validation on uploads.
