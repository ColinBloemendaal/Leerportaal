---
version: 2026-08-04
---

# Sub-processors

Third parties the platform uses that may process personal data on the platform's behalf, as referenced by [`docs/dpa.md`](dpa.md) Section 6. Kept up to date as the actual integrations in this codebase change -- this list should never drift from what `.env.example`/`config/*.php` actually wire up.

| Sub-processor | Purpose | Data involved | Location |
| --- | --- | --- | --- |
| Hetzner Online GmbH | Application hosting, database, Redis, object storage | All platform data at rest | EU (Germany) |
| Ploi.io | Server provisioning and deployment automation | Server access; no application data processed directly | EU |
| Mollie B.V. | Payment processing for reseller invoices | Billing/payment data for the transaction being processed | EU (Netherlands) |
| Mailgun (Sinch) | Transactional email delivery (notifications, invites, password resets) | Recipient email address, message content | US/EU (region-configurable) |
| Sentry (Functional Software, Inc.) | Application error tracking | Error/exception context, with PII explicitly redacted before reporting -- see CLAUDE.md §7 and the Sentry configuration in `bootstrap/app.php`/`config/sentry.php` | US/EU (region-configurable) |

Each of these has, or will have, its own data processing agreement with the platform operator, consistent with the platform's own obligations to its resellers under `docs/dpa.md`.

## Removing or changing a sub-processor

Update this file in the same commit as the code change that adds/removes the integration, and bump the `version` header above so it's clear the list itself changed (independent of `docs/dpa.md`'s own version). This list does not require re-acceptance of the DPA on its own -- only a change to `docs/dpa.md`'s actual text does (see `config('gdpr.dpa_version')`) -- but resellers should be able to see the current list at any time regardless.
