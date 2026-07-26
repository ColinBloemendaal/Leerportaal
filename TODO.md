# Leerportaal — Build Plan

Work top to bottom. Do not start a phase until the previous one is fully checked.
Read `CLAUDE.md` before starting any task.

Legend: `[ ]` todo · `[x]` done · `[~]` in progress · `[!]` blocked (add a note)

---

## Phase 0 — Foundation

- [x] `composer create-project laravel/laravel` — PHP 8.3, Laravel 12
- [x] Git init, `main` protected, branch naming per CLAUDE.md §10 — branch protection skipped by explicit human decision (solo work, direct commits to `main` allowed for now)
- [x] Install Inertia (server + client) + Vue 3 + TypeScript + Vite
- [x] Install Bootstrap 5.3 + Sass. Create `resources/sass/_variables.scss` and `app.scss`
- [x] Confirm no Tailwind anywhere in the project (remove Laravel's default)
- [x] Install Pint, Larastan (level 8), Pest, Pest coverage plugin — coverage driver (Xdebug/PCOV) not installed locally on this machine; wired into CI instead
- [x] Install ESLint, Prettier, `vue-tsc`
- [x] Add `composer check` / `composer fix` / `npm run lint` / `npm run types` scripts
- [x] GitHub Actions CI: Pint, Larastan, Pest with 85% coverage gate, ESLint, vue-tsc
- [x] Configure `.env.example` with every key the project needs
- [x] `Model::preventLazyLoading()` + `preventSilentlyDiscardingAttributes()` in non-production
- [x] Create the layer directories from CLAUDE.md §3 (`Actions`, `Contracts`, `DataTransferObjects`, `Repositories/Eloquent`, `Services`, `Http/Resources`, `Enums`, `Exceptions`)
- [x] `RepositoryServiceProvider` with interface → implementation binding convention
- [x] Base classes/interfaces: `QuestionType`, `PaymentGateway` stub, base repository interface pattern
- [x] `tests/Architecture/` Pest arch tests enforcing CLAUDE.md §3a (see §9)
- [x] `tests/Fakes/` directory + fake for every external-service interface
- [!] One vertical slice as a reference implementation (FormRequest → DTO → Action → Repository → Inertia Resource) for later tasks to copy — deferred to Phase 1 by human decision: every real concept to slice through belongs to a later phase, and a throwaway non-domain example couldn't demonstrate tenancy scoping (CLAUDE.md §2). Moved to Phase 1 below, once the first tenant-scoped model exists.
- [x] Base Inertia layouts: `GuestLayout`, `AppLayout`, `AdminLayout` (Bootstrap, no theming yet)
- [x] Error pages (403/404/419/500) as Inertia pages
- [!] Ploi deployment script + zero-downtime deploy, staging environment on Hetzner — deploy script and docs done (`deploy/ploi-deploy.sh`, `docs/deployment.md`); actually provisioning the Hetzner server, Ploi site, and staging environment needs your Ploi/Hetzner account access — see `docs/deployment.md` for the exact steps
- [!] Queue worker (Redis) + Horizon, scheduler cron via Ploi — Horizon installed and configured, `QUEUE_CONNECTION=redis`; the actual queue worker daemon and scheduler cron registration are Ploi dashboard actions — see `docs/deployment.md`
- [!] Sentry (or equivalent) with PII redaction configured — SDK installed and wired (`send_default_pii => false`, `SentryPiiRedactor` before_send hook, exception handler integration); a real `SENTRY_LARAVEL_DSN` needs your Sentry account

---

## Phase 1 — Tenancy, auth, roles

### Tenancy core

- [x] `resellers` table + model (name, slug, status, settings, soft deletes)
- [x] `App\Tenancy\TenantContext` scoped singleton
- [x] `App\Scopes\TenantScope` global scope
- [x] `App\Concerns\TenantScoped` trait (scope + `creating` stamp) — Larastan flags an unused trait with no consumer, so `resellerklanten` (table + model only, see below) was pulled forward as its first real consumer, with an isolation test in `tests/Tenancy/`
- [x] `ResolveTenant` middleware: custom domain → cookie → fallback — both steps now fully wired (this task added the custom domain step)
- [x] `/login/{slug}` route that resolves reseller and sets the tenant cookie
- [x] Custom domain table + verification flow (DNS CNAME check)
- [!] Automated LetsEncrypt issuance for custom domains (Ploi API) — interface, Action, event/listener wiring, and fake all done and tested; `HttpPloiClient`'s endpoint/payload are written to best understanding of Ploi's API but marked `VERIFY:` since untested against a live account — needs your Ploi credentials to confirm
- [x] Unbranded fallback experience for bare `leerportaal.nl`
- [x] `tests/Tenancy/` helper: `actingAsReseller()`, `assertTenantIsolated()`
- [x] Artisan command `tenancy:audit` — fails if any tenant table lacks `reseller_id` or the trait

### Users & auth

- [x] `users` table: one table, one guard, `reseller_id` nullable (null = platform staff)
- [x] `resellerklanten` table + model, `reseller_id` scoped — done early (Tenancy core section above)
- [x] `users.resellerklant_id` nullable FK
- [x] Reference vertical slice (FormRequest → DTO → Action → Repository → Inertia Resource) built around `resellerklanten` — index + create, `/klanten`, now gated behind real `auth` middleware
- [x] Login, logout, password reset, email verification (Fortify or hand-rolled) — hand-rolled by human decision, using the same Action/FormRequest/Controller pattern as everywhere else; login is tenant-aware (branded domain restricts to that reseller + platform staff, unbranded restricts to platform staff only)
- [x] 2FA (TOTP + recovery codes). Mandatory for platform roles, optional for others. `reseller_id === null` used as a proxy for "platform role" until task 34 (spatie/laravel-permission) exists
- [x] Rate limiting on all auth endpoints
- [x] Session security: regeneration, secure cookies, sensible lifetime. Regeneration was already in place from the login/2FA/logout work; added a production-defaulting `secure` cookie flag (defense in depth even if the host env var is never set) and kept the 120-minute default lifetime
- [x] Invite flow: reseller/klant invites user by email, signed branded link. `user_invites` table (tenant-scoped) + `App\Enums\Role` (the full task-34 role list, stored but not yet enforced -- task 34 should seed these exact cases rather than reinventing them). Accept link carries the reseller's slug (`/invite/{reseller}/accept/{invite}/{hash}`) so the guest's request can re-resolve TenantContext before any tenant-scoped read happens, same bootstrapping idea as `/login/{slug}` -- avoids ever needing `withoutTenantScope()` for a non-admin context

### Roles & permissions

- [x] Install `spatie/laravel-permission`, teams mode, `team_foreign_key = reseller_id`. `TenantContext::set()` now also calls `PermissionRegistrar::setPermissionsTeamId()`, so every call site that resolves tenant (middleware and the invite-accept flow) keeps spatie's team scoping in sync automatically. Open question before role seeding: `model_has_roles`/`model_has_permissions.reseller_id` is NOT NULL (and part of the composite PK) once teams mode is on, so platform staff (`reseller_id` null) cannot be assigned any spatie role as currently configured -- needs a decision before task 35
- [x] Seed roles: `super-admin`, `platform-admin`, `platform-author`, `support`, `reseller-owner`, `reseller-admin`, `reseller-author`, `reseller-reporter`, `klant-admin`, `klant-manager`, `cursist`. Resolved the open question from the previous line by human decision: platform roles (`super-admin`, `platform-admin`, `platform-author`, `support`) are stored on `users.platform_role` directly, not as spatie roles -- avoids fighting teams mode's NOT NULL team column. The 7 reseller/klant roles are real spatie roles, auto-seeded per reseller via a `ResellerCreated` event fired from `Reseller`'s `created` model event (`App\Actions\Permissions\SeedRolesForReseller`); `permissions:sync-roles` artisan command backfills any reseller that predates this wiring
- [x] Seed granular permissions (courses._, users._, reports._, billing._, impersonate). `App\Enums\Permission` is the catalog, seeded as global spatie Permission rows via a data migration (not a seeder -- must exist via `php artisan migrate` alone, matching how deploys actually run). Not assigned to any role yet: courses/reports/billing have no model or Policy to consume them until later phases, so there's nothing to assign them to yet
- [x] Base `Policy` for every model. `Gate::before` for super-admin only. Added the 3 missing policies (`CustomDomain`, `User`, `Reseller`) following the same "placeholder until roles exist" pattern as the existing ones. `Gate::before` lives in `App\Policies\SuperAdminBypass` (an invokable class), not inline in `AppServiceProvider`, so the `User` type-hint it needs stays inside the arch whitelist without having to widen it to all of `App\Providers`
- [x] Feature tests: each role can/cannot reach each area. Tests today's actual (coarse) model, by human decision: the 7 team roles are seeded but nothing checks them yet (every policy still checks reseller_id !== null / platform_role), so they're genuinely interchangeable right now -- confirmed via `/klanten` and `/invites` for every team role, no-role, every platform_role, and the super-admin bypass. Role-specific access is a later-phase concern once real features have policies that check `hasRole()`/permissions

### Audit & impersonation

- [x] Install `spatie/laravel-activitylog`
- [x] `HasAuditLog` trait, applied to all user/content/billing models. Applied to every current model (`User`, `Reseller`, `ResellerKlant`, `UserInvite`, `CustomDomain`) -- no content/billing models exist yet, future ones should pick up the trait too. Uses `logUnguarded()` since every model uses `$guarded = []` rather than `$fillable`. Secrets (password, remember_token, two_factor_secret, two_factor_recovery_codes) are excluded globally via `config('activitylog.default_except_attributes')`, not per model, so nothing new can accidentally leak them by reusing a column name
- [x] Impersonation: start/stop, reason required, timestamped, session limit, UI banner. Hierarchy by human decision: super-admin -> any reseller-side user; reseller staff -> their own klanten/cursisten (not fellow staff); klant-admin/klant-manager -> cursisten in their own klant only (the first real consumer of the spatie team roles seeded back in task 34/35). 15-minute hard session limit, enforced by `EnforceImpersonationSessionLimit` middleware in the `web` group. Platform staff can never be impersonated
- [x] Block password change, billing, and permission changes while impersonating. `BlockDuringImpersonation` middleware (alias `block-during-impersonation`) built and tested, but not attached to any route yet -- none of those three areas has a route in this phase (self-service password change, billing, and permission-management UI are all later phases). Attach it to their routes once they exist
- [ ] Soft deletes on all user-facing models + restore actions

---

## Phase 2 — Whitelabel Level 1

- [ ] `reseller_themes` table: primary/secondary/accent colors, font family, logo, favicon, login background
- [ ] Runtime CSS custom property injection into layouts (no per-tenant build)
- [ ] Bootstrap `_variables.scss` wired to CSS custom properties
- [ ] Theme editor UI with live preview
- [ ] Contrast validation — warn when a chosen color pair fails WCAG AA
- [ ] Logo/favicon upload with dimension + type validation, private disk
- [ ] Optional custom CSS field, sanitized, with a hard character limit
- [ ] Per-reseller email branding: logo, colors, sender name, reply-to
- [ ] Per-reseller email templates (overridable per notification type)
- [ ] Configurable footer content, support email, terms/privacy URLs
- [ ] Feature test: reseller A's theme never leaks into reseller B's render

---

## Phase 3 — Course content

- [ ] `course_categories` (nested, platform-level + reseller-level)
- [ ] `courses` table: title, slug, description, status, owner type (platform/reseller), `reseller_id` nullable, `available_locales`, `variant_year`, `repeats_from_course_id`, price fields
- [ ] `modules` → `lessons` → `blocks` hierarchy, all ordered
- [ ] Block types: rich text, image, video embed, file/download, embed, divider, callout
- [ ] Install `spatie/laravel-translatable`; translatable fields as JSON
- [ ] Per-field language dropdown in the editor (per `available_locales`)
- [ ] Translation completeness indicator; block publish in a locale until complete
- [ ] Draft → review → published workflow with versioning
- [ ] Course duplication (within a reseller, and platform-internal)
- [ ] Media library per reseller, S3-compatible storage, private disk + signed URLs
- [ ] Storage usage metering per reseller (5 GB included)
- [ ] Video: external hosting (Vimeo/Mux/YouTube unlisted). Do not self-host video
- [ ] Course prerequisites and ordering rules
- [ ] Estimated duration, learning objectives, tags
- [ ] Catalog courses are **read-only** for resellers — enforced by policy + tested

---

## Phase 4 — Questions & assessment

- [ ] `questions` table per CLAUDE.md §5
- [ ] `QuestionType` interface, `QuestionTypeRegistry`, `QuestionTypeEnum`
- [ ] `quizzes` table: type (practice/exam), settings, `lesson_id` or `module_id`
- [ ] Implement type: `multiple_choice`
- [ ] Implement type: `multiple_response` (with partial credit)
- [ ] Implement type: `true_false`
- [ ] Implement type: `open_short` (keyword/regex matching)
- [ ] Implement type: `essay` (manual grading + rubric)
- [ ] Implement type: `matching`
- [ ] Implement type: `ordering`
- [ ] Implement type: `fill_in_blank` (cloze)
- [ ] Implement type: `dropdown_in_text`
- [ ] Implement type: `numeric` (with tolerance)
- [ ] Implement type: `hotspot_image`
- [ ] Implement type: `drag_drop_image`
- [ ] Implement type: `likert` (non-scored)
- [ ] Implement type: `file_upload`
- [ ] Keyboard + ARIA alternative for every drag/hotspot type — a type is not done without it
- [ ] Question bank per reseller, reusable across quizzes
- [ ] Exam settings: time limit, attempt limit, pass threshold, retake rules, question pool size
- [ ] Question and answer randomization
- [ ] `quiz_attempts` + `question_answers`, full answer history retained
- [ ] Grading service, auto + manual, with regrade capability
- [ ] Feedback per answer and per question, shown per configurable rules
- [ ] Unit tests per type: correct / incorrect / partial / empty

---

## Phase 5 — Enrollment, progress, certificates

- [ ] `course_assignments` table: user, course, assigned_by, assigned_at, first_opened_at, revoked_at, billing state
- [ ] Assignment UI: individual, bulk, by group
- [ ] `groups` per resellerklant, group-based assignment
- [ ] Progress tracking per block/lesson/module, resume where left off
- [ ] Completion rules per course (all lessons / pass exam / minimum score)
- [ ] Deadlines, reminder schedule, overdue flags
- [ ] Certificates: PDF generation, per-reseller template, unique verification code
- [ ] Public certificate verification page
- [ ] Certificate expiry + renewal (drives repetition courses)
- [ ] Repetition courses: `variant_year` + `repeats_from_course_id`, "which editions has this cursist done" report
- [ ] "Voorlees" — browser `SpeechSynthesis`: play/pause/stop, speed, per-block and per-question, language follows content locale
- [ ] Cursist dashboard: assigned, in progress, completed, deadlines

---

## Phase 6 — Access control matrix

- [ ] Platform → reseller: grant catalog course or whole category access
- [ ] Reseller → resellerklant: which courses a klant may assign
- [ ] Klant → cursist: actual assignment (Phase 5)
- [ ] Category-level grants that cascade to new courses added later
- [ ] Access changes are audit-logged with before/after
- [ ] Revoking access does not delete progress or certificates
- [ ] Effective-access debug view: "why can this user see this course?"
- [ ] Tests for every level of the matrix

---

## Phase 7 — Admin panel & reporting

- [ ] Platform admin dashboard: resellers, users, courses, revenue, storage
- [ ] Reseller admin dashboard: klanten, cursisten, assignments, spend
- [ ] Klant dashboard: cursisten, progress, completions
- [ ] Generic filter/sort/search layer reusable across every index (query builder + saved filters)
- [ ] Filterable index for: users, resellers, klanten, courses, assignments, attempts, invoices, activity
- [ ] Activity log viewer: filter by actor, subject, action, date range, reseller
- [ ] Per-user detail page: full timeline of everything they did
- [ ] Impersonation entry points from user detail pages
- [ ] Exports: CSV/XLSX for every index, queued for large sets, expiring signed download links
- [ ] Scheduled reports emailed to klant admins
- [ ] Platform health: queue depth, failed jobs, storage, error rate

---

## Phase 8 — Notifications, billing, GDPR

### Notifications

- [ ] Notification catalogue (welcome, invite, assignment, deadline, overdue, completion, certificate, password, billing, admin alerts)
- [ ] Email + in-app database channel for each
- [ ] In-app notification centre with read state
- [ ] Per-user notification preferences, per type, per channel
- [ ] Digest option (daily/weekly) instead of individual emails
- [ ] Reseller-branded email templates applied to all of the above
- [ ] Bounce/complaint handling, suppression list

### Billing

- [ ] `PaymentGateway` interface + `MollieGateway` implementation
- [ ] Normalized webhook handler (idempotent, signature-verified, replay-safe)
- [ ] Billable event on course assignment
- [ ] Pricing: `max(15% × reseller_price, €3.00)` for reseller courses; fixed platform price for catalog courses
- [ ] Platform admin manual price-floor override per reseller course
- [ ] 14-day revocation: free only if `first_opened_at` is null and within window
- [ ] Authoring add-on subscription (€250/year) gating custom course creation
- [ ] Storage overage metering and charging beyond 5 GB
- [ ] Invoice generation (immutable), credit notes for corrections
- [ ] VAT handling incl. reverse charge / VAT ID validation
- [ ] Dunning: failed payment retries, suspension rules
- [ ] Reseller billing dashboard: current period, breakdown per klant, history
- [ ] Exhaustive unit tests on every pricing calculation

### GDPR

- [ ] Data export per data subject: JSON + human-readable
- [ ] Erasure with anonymization strategy (preserve invoices/audit as legally required)
- [ ] Retention policies per data type, enforced by scheduled job, configurable per reseller
- [ ] Cookie consent (functional-only default, no non-essential without consent)
- [ ] Per-reseller DPA acceptance and record
- [ ] `docs/subprocessors.md` maintained and surfaced in-app
- [ ] Processing register documentation
- [ ] Breach response runbook in `docs/`
- [ ] Confirm PII never reaches application logs or error reports

---

## Phase 9 — Whitelabel Level 2 (block-based pages)

- [ ] Fixed page templates: home, course overview, login, about, contact
- [ ] Configurable content blocks: hero, text, course grid, testimonial, CTA, FAQ, logo strip
- [ ] Reorder blocks in a list (not free canvas), toggle visibility
- [ ] Per-block content editing with translations
- [ ] Preview before publish, draft/published page state
- [ ] Navigation menu builder
- [ ] Per-page SEO fields
- [ ] Confirm Level 3 free-canvas dragging remains out of scope

---

## Phase 10 — AI assisted authoring

- [ ] Provider abstraction behind an interface (no direct vendor calls in domain code)
- [ ] Generate course outline from a topic/brief
- [ ] Generate lesson draft from an outline node
- [ ] Generate draft questions from existing lesson content
- [ ] Suggest distractors for multiple choice
- [ ] Translate existing content into an enabled locale (draft, human-reviewed)
- [ ] Rewrite/simplify text to a target reading level
- [ ] **Everything lands as draft. Nothing auto-publishes. Human review mandatory**
- [ ] Clear AI-generated flag on content, visible to the author
- [ ] Usage metering and per-reseller quota
- [ ] Cost controls and rate limits
- [ ] Prompt-injection hardening on user-supplied source material

---

## Phase 11 — Standards & integrations

- [ ] xAPI statement emission to a configurable external LRS (do this first — cheapest, real value)
- [ ] LTI 1.3 / Advantage as Tool: OIDC launch, JWKS, deep linking, names & roles, grade passback (AGS)
- [ ] LTI platform registration UI per reseller
- [ ] SCORM 1.2 / 2004 package import: upload, unzip, manifest parse, player, CMI runtime, progress mapping
- [ ] SSO: SAML 2.0 and/or OIDC per reseller
- [ ] SCIM user provisioning (only if a customer requires it)
- [ ] Webhooks out for key events (assignment, completion, certificate)
- [ ] Public API — **only when a real consumer exists**

---

## Ongoing (not a phase — do continuously)

- [ ] Keep Larastan baseline shrinking, never growing
- [ ] Keep coverage above 85%
- [ ] Accessibility audit each phase (WCAG 2.1 AA target)
- [ ] Performance: index review, N+1 checks, slow query log
- [ ] Dependency updates + security advisories
- [ ] Keep `docs/` current (architecture decisions, runbooks, subprocessors)
