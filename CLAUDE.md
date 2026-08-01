# CLAUDE.md — Leerportaal

Whitelabel LMS. Laravel + Inertia + Vue 3 + Bootstrap 5.3. Multi-tenant, single database.

---

## 0. How to work on this project

**Read `TODO.md` before starting anything.** It is the single source of truth for build order. Work through it strictly top to bottom.

Rules:

1. Work on **one task at a time**. Do not batch tasks from different phases.
2. Do not start a task in phase N+1 while phase N has unchecked tasks, unless the human explicitly says so.
3. When a task is done: run `composer check` (see §9), then mark the task `[x]` in `TODO.md` in the same commit as the code.
4. If a task turns out to be bigger than one commit, split it in `TODO.md` into sub-tasks first, then work through them.
5. If a task is blocked or ambiguous, **stop and ask**. Do not guess at product decisions. Do not silently pick an interpretation.
6. If you discover work that isn't in `TODO.md`, add it to the correct phase rather than doing it inline.
7. Never mark a task complete without tests passing and static analysis clean.
8. **Never start a live browser session (dev server preview, `artisan serve`, `npm run dev`, etc.) to test the app.** The human does this testing themselves. Verify with `composer check` / static analysis / automated tests instead.

Every commit must leave `main` deployable.

---

## 1. Architectural decisions (settled — do not revisit without asking)

| Decision          | Choice                                                                                                                        |
| ----------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| Tenancy           | **Single database**, `reseller_id` column on every tenant-owned table                                                         |
| Tenant isolation  | Global Eloquent scope via `TenantScoped` trait + mandatory isolation test per model                                           |
| Tenant resolution | Middleware chain: custom domain → tenant cookie → `/login/{slug}` → unbranded fallback                                        |
| Frontend          | Inertia + Vue 3 + TypeScript. **No separate API, no SPA-with-Sanctum**                                                        |
| CSS               | **Bootstrap 5.3 + Sass. Never Tailwind**                                                                                      |
| Theming           | CSS custom properties injected at runtime. **No per-tenant CSS build step**                                                   |
| Auth              | One `users` table, one guard, roles via `spatie/laravel-permission` in teams mode                                             |
| Roles team key    | `reseller_id`                                                                                                                 |
| Question types    | One `questions` table + one PHP class per type + one Vue component per type                                                   |
| Course structure  | One model: Course → Module → Lesson → Block. A "quick" e-learning is a course with one module                                 |
| Translations      | `spatie/laravel-translatable`, JSON columns. Per-course `available_locales`                                                   |
| Page builder      | **Level 1 only** (theme config). Level 2 (block templates) is Phase 9. **Level 3 (free drag & drop) is out of scope forever** |
| Catalog courses   | Read-only for resellers. No forking, no copy-on-write                                                                         |
| Klant branding    | Inherits reseller branding. Theming exists at reseller level only                                                             |
| Cursist ↔ klant   | One klant per cursist. **No transfer feature**                                                                                |
| Payments          | Mollie, behind a `PaymentGateway` interface                                                                                   |
| TTS ("voorlees")  | Browser `SpeechSynthesis` API. No server-side TTS in v1                                                                       |
| Deleting          | Soft deletes on everything user-facing                                                                                        |
| Infra             | Hetzner + Ploi. **No Docker anywhere**                                                                                        |
| AI authoring      | Phase 10. Assisted only — never auto-publish                                                                                  |

---

## 2. Tenancy — the most important rule in this codebase

Every tenant-owned model uses the `TenantScoped` trait:

```php
namespace App\Concerns;

trait TenantScoped
{
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope(new \App\Scopes\TenantScope);

        static::creating(function (self $model): void {
            $model->reseller_id ??= app(\App\Tenancy\TenantContext::class)->id();
        });
    }
}
```

Rules:

- Every tenant-owned table has `reseller_id` (`unsignedBigInteger`, indexed, FK to `resellers`).
- **Never** write `where('reseller_id', ...)` manually. The scope does it.
- Escaping the scope requires `->withoutTenantScope()`. This is greppable. Every use must have a comment explaining why, and must be in an admin/platform context.
- The current tenant lives in `App\Tenancy\TenantContext`, a scoped singleton resolved once per request by middleware. Never resolve tenant from the request inside a model or service.
- **Every tenant-scoped model gets an isolation test** proving reseller A cannot read reseller B's rows. No exceptions. See §8.

Platform-owned models (catalog courses, resellers, platform users) do **not** use the trait.

---

## 3. Directory layout

```
app/
  Actions/                  Writes. One invokable class per operation
    Courses/AssignCourseToCursist.php
  Concerns/                 Traits (TenantScoped, HasAuditLog, ...)
  Contracts/                Interfaces, grouped by domain
    Billing/PaymentGateway.php
    Repositories/CourseRepository.php
  DataTransferObjects/      Immutable readonly DTOs
    Courses/CourseData.php
  Enums/                    Backed string enums
  Exceptions/               Domain exceptions
  Http/
    Controllers/            Thin. Resource controllers only
    Middleware/
    Requests/               One FormRequest per write endpoint
    Resources/              Inertia prop shaping (see §4.6)
  Models/
  Policies/
  Providers/
    RepositoryServiceProvider.php   Interface → implementation bindings
  Questions/
    Contracts/QuestionType.php
    Types/                  One class per question type
    QuestionTypeRegistry.php
  Repositories/             Reads. Implementations of Contracts\Repositories
    Eloquent/EloquentCourseRepository.php
  Scopes/
  Services/                 Stateless domain services (orchestration)
    Billing/AssignmentPricingService.php
  Support/                  Value objects, helpers (Money, ...)
  Tenancy/
resources/
  js/
    Pages/                  Inertia pages, mirror route structure
    Components/
    Questions/
      Editor/               One component per type
      Player/               One component per type
    Composables/
    types/                  TypeScript types
  sass/
    _variables.scss         Bootstrap overrides
    app.scss
  lang/
tests/
  Feature/
  Unit/
  Tenancy/                  Isolation tests
```

---

## 3a. Layering contract (follow this exactly — no improvising)

Each layer has one job. If you're unsure where code belongs, it belongs in the layer whose job description matches it. If none match, ask.

```
Route → Middleware → FormRequest → Controller → Action │ Service → Repository → Model
                                        ↓                    ↓
                                  Inertia Resource         DTO
```

### Responsibility table

| Layer           | Owns                                                                      | Never does                                     |
| --------------- | ------------------------------------------------------------------------- | ---------------------------------------------- |
| **FormRequest** | Validation rules, authorization gate, `toDto()`                           | Business logic, DB writes                      |
| **Controller**  | HTTP wiring: call one Action/Service, return response                     | Validation, business logic, query building     |
| **Action**      | Exactly one **write** operation, transactional                            | Reads beyond what it needs, HTTP concerns      |
| **Service**     | Orchestration across multiple Actions/Repositories; stateless calculation | Being a dumping ground for loose functions     |
| **Repository**  | **Reads**: queries, filters, pagination                                   | Writes, business rules                         |
| **DTO**         | Typed, immutable data between layers                                      | Behaviour, DB access                           |
| **Model**       | Relationships, casts, accessors, scopes                                   | Business logic, external calls                 |
| **Policy**      | Authorization decisions                                                   | Tenant scoping (that's the global scope's job) |
| **Enum**        | A fixed set of values + behaviour tied to those values                    | Storing mutable state                          |

### Repositories — reads only

Repositories exist so query logic has one home and can be swapped in tests. They are **not** a wrapper around every Eloquent method.

Rules:

- Every repository has an interface in `App\Contracts\Repositories` and an Eloquent implementation in `App\Repositories\Eloquent`.
- Bound in `RepositoryServiceProvider`. **Always type-hint the interface**, never the implementation.
- Methods return models, collections, paginators, or DTOs — never query builders. A leaked builder means the caller can bypass the repository.
- **Do not** write pass-through methods (`find`, `all`, `create`) that add nothing over Eloquent. If a repository method has one line and no logic, it shouldn't exist.
- A repository is created when a query is used in more than one place, or is complex enough to test on its own. Not before.
- Repositories never write. Writes are Actions.

```php
interface CourseRepository
{
    public function publishedForReseller(int $resellerId): Collection;
    public function paginateForAdmin(CourseFilterData $filters): LengthAwarePaginator;
    public function findAssignableBy(User $user, int $courseId): ?Course;
}
```

### Actions — writes only

- One class, one operation, `__invoke()`, `final`, dependencies injected via constructor.
- Wraps its own DB transaction when it touches more than one table.
- Takes a **DTO**, never a `Request`, never a raw array.
- Returns the created/updated model or a result DTO. Never a response.
- Dispatches domain events; does not send notifications directly.
- Named verb-first: `AssignCourseToCursist`, `PublishCourse`, `RevokeAssignment`.

```php
final readonly class AssignCourseToCursist
{
    public function __construct(
        private AssignmentPricingService $pricing,
        private CourseRepository $courses,
    ) {}

    public function __invoke(AssignCourseData $data): CourseAssignment
    {
        return DB::transaction(function () use ($data): CourseAssignment { /* ... */ });
    }
}
```

### Services — orchestration and calculation

- Stateless. No properties holding request state.
- Use a service when logic spans multiple Actions or Repositories, or is a pure calculation worth isolating (pricing, grading, progress).
- Named for what they do: `AssignmentPricingService`, `GradingService`, `StorageMeteringService`.
- **Do not** create a service per model. `CourseService` as a bag of unrelated methods is banned.
- Anything with an external dependency (payments, AI, mail providers, storage) sits behind an interface in `App\Contracts` so it can be swapped and faked.

### DTOs

- `final readonly` classes in `App\DataTransferObjects`, all properties typed and promoted.
- Named `<Thing>Data`. Constructed via `from...()` named constructors.
- Cross every layer boundary as DTOs, not arrays. **No associative arrays as informal structs.**
- FormRequests expose `toDto()`; controllers pass the DTO straight to the Action.

```php
final readonly class AssignCourseData
{
    public function __construct(
        public int $courseId,
        public int $userId,
        public int $assignedByUserId,
        public ?CarbonImmutable $deadline = null,
    ) {}
}
```

### Interfaces

Create an interface when there is a real reason for a second implementation: an external service, a swappable strategy, or something that must be faked in tests. Do not create an interface for every class — a one-implementation interface that will never have a second is noise.

Mandatory interfaces: `PaymentGateway`, `QuestionType`, all repositories, AI provider, storage metering, TTS (if server-side is ever added).

### FormRequests

- One per write endpoint, named `<Action><Model>Request`.
- `authorize()` delegates to the Policy — never inline role checks.
- `rules()` uses enum/rule objects, not loose strings, where a fixed set exists.
- `toDto()` is required. Controllers do not read `$request->input()` directly.
- Validation messages come from lang files; never hardcoded.

### Enums

- Always backed by **string**, never int.
- Live in `App\Enums`, singular name.
- Behaviour lives on the enum: `label()`, `color()`, `isTerminal()`. Do not scatter `match` statements across the codebase.
- Cast on the model. Never compare against raw strings anywhere.

### Controllers

```php
public function store(StoreCourseRequest $request, CreateCourse $createCourse): RedirectResponse
{
    $course = $createCourse($request->toDto());

    return to_route('courses.show', $course)->with('success', __('courses.created'));
}
```

If a controller method is longer than about 10 lines, logic has leaked into the wrong layer.

### Inertia props

- Shape props in an `App\Http\Resources` class or a DTO — never pass raw models to Inertia.
- Every page's props have a matching TypeScript type in `resources/js/types`.
- Never expose a model attribute the page doesn't use. Passing whole models leaks columns you didn't intend to publish.

---

## 4. PHP conventions

- PHP 8.5+. `declare(strict_types=1);` at the top of every file.
- Type everything: params, returns, properties. No `mixed` unless genuinely unavoidable.
- Final classes by default. Only remove `final` when something actually extends it.
- Constructor property promotion. Readonly where possible.
- **No facades in domain code.** Inject dependencies. Facades are acceptable in controllers and migrations only.
- **Controllers are thin.** Validate (FormRequest) → call Action/Service → return Inertia response. No business logic, no query building beyond simple index listings.
- Writes go through `App\Actions`. One action, one job, `__invoke()`.
- No `env()` outside `config/`. Ever.
- Enums for every fixed set of values. Backed by string, never int.
- Money is stored as integer cents in a `_cents` column. Never float. Use a `Money` value object.
- Dates are always `CarbonImmutable`, always UTC in the database.

### Naming

| Thing         | Convention                     | Example                                             |
| ------------- | ------------------------------ | --------------------------------------------------- |
| Model         | Singular PascalCase            | `CourseAssignment`                                  |
| Table         | Plural snake_case              | `course_assignments`                                |
| Migration     | Descriptive                    | `2026_01_04_120000_add_variant_year_to_courses.php` |
| Controller    | Plural resource + `Controller` | `CourseAssignmentController`                        |
| Action        | Verb-first                     | `AssignCourseToCursist`                             |
| FormRequest   | Action + `Request`             | `StoreCourseRequest`                                |
| Policy        | Model + `Policy`               | `CoursePolicy`                                      |
| Enum          | Singular                       | `QuestionType`                                      |
| Vue page      | PascalCase, mirrors route      | `Pages/Courses/Edit.vue`                            |
| Vue component | PascalCase, multi-word         | `CourseProgressBar.vue`                             |

### Eloquent

- Never `$fillable`. Use `$guarded = []` plus FormRequest validation, or explicit assignment in Actions.
- Always define return-typed relationships: `public function modules(): HasMany`.
- No queries in Blade or Vue. Load in the controller, pass as props.
- Eager load explicitly. `Model::preventLazyLoading()` is enabled in non-production — a lazy load is a bug.
- Scopes for reusable query fragments, named `scopePublished()` etc.

---

## 5. Question types

One `questions` table:

```
id, quiz_id, type (enum), prompt (json/translatable), points,
order, settings (json), payload (json), timestamps, deleted_at
```

Each type is a class in `App\Questions\Types` implementing:

```php
interface QuestionType
{
    public static function key(): QuestionTypeEnum;
    public static function label(): string;
    public function payloadRules(): array;        // validation for payload json
    public function editorComponent(): string;    // Vue component name
    public function playerComponent(): string;
    public function grade(Question $q, mixed $answer): GradeResult;
    public function isAutoGradable(): bool;
}
```

Registered in `QuestionTypeRegistry`. Adding a type = one enum case + one PHP class + two Vue components. Nothing else changes.

Types to implement (Phase 4):

`multiple_choice`, `multiple_response`, `true_false`, `open_short`, `essay`, `matching`, `ordering`, `fill_in_blank`, `dropdown_in_text`, `numeric`, `hotspot_image`, `drag_drop_image`, `likert`, `file_upload`

**Accessibility is not optional.** Every drag & drop and hotspot type must have a full keyboard alternative and correct ARIA. A type without a keyboard path is not done.

---

## 6. Vue / frontend conventions

- Vue 3 `<script setup lang="ts">` only. No Options API, no plain JS components.
- Props and emits typed via `defineProps<T>()` / `defineEmits<T>()`. No runtime prop objects.
- Composables in `resources/js/Composables`, prefixed `use`.
- **Bootstrap classes first.** Custom CSS only when Bootstrap genuinely can't do it, and then scoped to the component.
- Theming: reseller colors/fonts are CSS custom properties on `:root`, injected server-side into the layout. Bootstrap variables in `_variables.scss` reference those properties.
- No inline styles except for values that are genuinely dynamic per-render.
- All user-facing strings via `vue-i18n`. No hardcoded Dutch or English in components.
- Forms use Inertia's `useForm`. Never hand-rolled fetch/axios for form submission.

---

## 7. Security & GDPR (strict — this is a legal requirement, not a nice-to-have)

- Every controller action is authorized by a Policy. `Gate::before` for super-admin only.
- Authorization is checked **in addition to** tenant scoping, never instead of it.
- Impersonation: platform staff only, logged with start/end timestamps and reason, hard session limit, visible banner in UI, never allows password change or billing actions while impersonating.
- Audit log (`spatie/laravel-activitylog`) on every model touching user data, course content, assignments, billing, and permissions.
- PII is never written to application logs. Redact in exception reporting.
- Data subject requests: export (JSON + human-readable) and erasure must be implemented as first-class admin actions, not manual SQL.
- Retention policy per data type, enforced by a scheduled job, configurable per reseller.
- Every reseller gets a DPA. Sub-processor list maintained in-repo at `docs/subprocessors.md`.
- All uploads scanned and validated by content type, never by extension. Private disk by default.
- Rate limit auth endpoints. 2FA available for all staff roles, mandatory for platform roles.

---

## 8. Testing

Pest. Tests are not optional and are written in the same commit as the code.

Required:

- **Feature test for every route.** Happy path + at least one authorization failure.
- **Isolation test for every tenant-scoped model** in `tests/Tenancy/`. Pattern: create data for reseller A and reseller B, act as A, assert B's rows are invisible on index, show, update, and delete.
- **Unit test for every question type's `grade()`** covering correct, incorrect, partial, and empty answers.
- **Unit test for every billing calculation.** These become invoices; they get exhaustive coverage.
- **Unit test for every Action** — success path plus each failure/validation branch.
- **Unit test for every Service** calculation method.
- **Test for every Repository method** that has real query logic, including that tenant scoping still applies through the repository.
- Policies tested directly.
- Interfaces get a fake implementation in `tests/Fakes` (e.g. `FakePaymentGateway`) so no test hits a live external service.

Rules:

- No mocking Eloquent. Use the database (SQLite in-memory or a MySQL test schema).
- Factories for all models. States for meaningful variants.
- Coverage gate: **85% minimum**, enforced in CI. Not a target — a floor.
- A failing test is never skipped or deleted to make CI green.

---

## 9. Tooling

```
composer check   # runs: pint --test, larastan, pest --coverage
composer fix     # runs: pint
npm run lint     # eslint + prettier
npm run types    # vue-tsc --noEmit
```

- **Laravel Pint** — Laravel preset. Zero diff before commit.
- **Larastan level 8.** Not 6. Baseline file allowed only for legacy at start; it must shrink, never grow.
- **ESLint + Prettier + vue-tsc.** No `any`. No `@ts-ignore` without a comment explaining why.
- CI runs all of the above on every PR. Red CI is never merged.

### Architecture enforcement

Use Pest's architecture testing (`tests/Architecture/`) to make §3a mechanically enforced rather than a matter of discipline:

- Controllers do not depend on `App\Models` directly (must go through Actions/Repositories/DTOs)
- Actions are `final`, `readonly`, and expose only `__invoke`
- Repositories implement their interface and live under `App\Repositories\Eloquent`
- Nothing outside `App\Repositories` and `App\Actions` calls Eloquent query methods
- DTOs are `final readonly` with no methods beyond named constructors
- Enums are string-backed
- `App\Services` and `App\Actions` contain no `Illuminate\Support\Facades` imports
- Every class in `App\Http\Requests` has a `toDto()` method

A rule that can be enforced in CI does not belong in a code review comment.

---

## 10. Git

- Branches: `feat/`, `fix/`, `chore/`, `refactor/` + short kebab description.
- Conventional commits: `feat(courses): add variant_year to course model`.
- One logical change per commit. The `TODO.md` checkbox update belongs in the commit that completes it.
- No commits directly to `main`.

---

## 11. Billing rules (implement exactly)

- Billable event = **assigning one course to one cursist**.
- Reseller-authored course: `max(15% × reseller_set_price, €3.00)`.
- Catalog course: fixed platform price, set by platform admin per course.
- Platform admin can set a manual **price floor override** on any reseller course. This is enforcement of a contractual clause, applied by a human. **Never attempt to auto-detect course similarity.**
- **Revocation:** free within 14 days _if the cursist has never opened the course_. After first open, or after 14 days, it is billed. Both conditions are timestamped and stored.
- **No reassignment.** A repeat is a new course variant (`variant_year`, `repeats_from_course_id`), assigned fresh, billed fresh.
- Storage: 5 GB included per reseller, metered beyond that.
- Authoring add-on: €250/year unlocks custom course creation.
- All amounts in integer cents. All calculations unit tested. Invoices are immutable once issued — corrections are credit notes.

---

## 12. Things that are explicitly out of scope

Do not build these. If they seem necessary, ask first.

- Free-canvas drag & drop page builder (Level 3)
- Course forking / reseller edits to catalog courses
- Cursist transfer between resellerklanten
- Server-side TTS
- Automated course-similarity detection
- Separate REST/GraphQL API for third parties (until a real consumer exists)
- Native mobile apps
