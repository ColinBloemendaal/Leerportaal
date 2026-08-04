---
version: 2026-08-04
---

# Data Breach Response Runbook

What to do when a personal data breach is suspected or confirmed. GDPR Article 33 requires notifying the relevant supervisory authority within 72 hours of becoming aware of a breach where feasible; Article 34 requires notifying affected data subjects directly when the breach is likely to result in a high risk to their rights and freedoms. This runbook exists so that clock doesn't start with "and now what?"

## 1. Detect

Likely first signals, in roughly descending order of how directly they point at a breach:

- A Sentry alert for an exception in a tenant-scoping or authorization code path (`TenantScope`, any Policy, `withoutTenantScope()` call site) -- these are the highest-signal category, since a bug there is exactly the shape of bug that leaks one reseller's data to another.
- An unexplained spike in `activity_log` entries for a reseller/user pattern that doesn't match normal usage (e.g. one account touching many other users' records in a short window).
- A `CheckPlatformHealthCommand` alert correlating with unusual queue/job activity.
- An external report (a reseller, a security researcher, a sub-processor's own breach notification to us).

## 2. Contain

- If a specific credential is compromised: force a password reset and revoke sessions for the affected account(s). There's no single "kill switch" action for this yet -- do it via direct DB update to `users.password`/clearing the `sessions` table rows for that `user_id`, the same table `App\Actions\Gdpr\EraseDataSubject` already clears rows from (see that Action for the exact query shape, though don't run the full erasure Action itself unless the account is actually being erased).
- If a vulnerability in a specific code path (e.g. a missing `withoutTenantScope()` guard, or a Policy that fails open): revert or hotfix immediately, ahead of any other step. A live vulnerability being actively exploitable is more urgent than documenting the incident.
- If a sub-processor is the source: contact them per their own incident-response terms; see `docs/subprocessors.md` for who they are.

## 3. Assess scope

- Which reseller(s)/user(s) were actually affected -- not "could have been," actually were, based on logs. `activity_log` and application logs are the primary evidence source; Sentry events for timing.
- Which categories of personal data were exposed -- see `docs/processing-register.md` Section 3 for what's actually stored where.
- Whether this rises to Article 34's "high risk" bar (special category data, credentials, financial data, or a large affected population all push toward yes).

## 4. Notify

- **Supervisory authority** (Article 33): within 72 hours of becoming aware, if there's any risk to data subjects. A platform staff member with knowledge of the incident is responsible for this -- there's no automated notification path, this is a human, legal-review action.
- **Affected resellers**: notify promptly regardless of the Article 34 threshold -- they are the data controller for their own klanten/cursisten and have their own downstream notification obligations under their own DPA with their klanten.
- **Affected data subjects directly** (Article 34): only if the high-risk bar is met, and typically coordinated with/through the affected reseller rather than platform staff contacting cursisten directly, since the reseller is the controller of that relationship.

## 5. Remediate and review

- Confirm the root cause is actually fixed (a regression test added if the cause was a code defect -- see CLAUDE.md §8's tenant isolation test requirement, which exists specifically to catch this class of bug before it ships).
- Record the incident and its timeline somewhere durable (this is intentionally not automated in-app; a breach response is a human process, not a feature).
- Review whether the retention policy, erasure scope, or audit logging documented in `docs/processing-register.md` need updating as a result.

## Roles

There is no dedicated "security team" structure yet in this codebase (no roles/permissions distinguish "incident responder" from any other platform staff -- see CLAUDE.md's own Phase 1 placeholder-policy comments throughout `app/Policies`). Until that exists, any platform staff member who detects or is informed of a suspected breach is responsible for escalating it and driving the process above; this section should be updated once a real on-call/incident-response role exists.
