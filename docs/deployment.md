# Deployment

Infra: Hetzner (server) + Ploi (provisioning/deploys). No Docker.

## One-time Ploi dashboard setup (human -- needs Ploi/Hetzner account access)

These are Ploi dashboard actions, not something scriptable from this repo:

1. **Create the server** on Hetzner via Ploi's server provisioning flow (PHP 8.3, MySQL or the chosen DB, Redis, Node for asset builds).
2. **Create the site** for this repo, connect the GitHub repository, set the branch to deploy.
3. Paste [`deploy/ploi-deploy.sh`](../deploy/ploi-deploy.sh)'s contents into Site -> **Deploy Script**. Keep the two in sync by hand -- Ploi does not read the file from the repo.
4. Enable **Zero-downtime deployment** in Site -> Settings. Ploi then builds each release in a fresh directory and atomically symlinks it into place once the deploy script exits `0`.
5. Enable the **Laravel Scheduler** toggle (Site -> Scheduler). This registers the `* * * * * php artisan schedule:run` cron entry Ploi manages -- nothing to add manually.
6. Add a **queue worker daemon** (Site -> Queue) once Horizon is installed: command `php artisan horizon`, with `php artisan horizon:terminate` added as a deploy script step so workers pick up new code on each release (graceful restart, not a hard kill).
7. Repeat steps 2-6 for a second, staging site pointed at a staging branch/subdomain, with its own database and `.env` -- this is the "staging environment" TODO item. Staging should mirror production configuration (§1 of CLAUDE.md) as closely as practical, scaled down.
8. Set every key from `.env.example` in the site's **Environment file** editor in Ploi, with real values (Mollie, Sentry, S3-compatible storage, Ploi API for LetsEncrypt automation, etc.).

## What's version-controlled here

- [`deploy/ploi-deploy.sh`](../deploy/ploi-deploy.sh) -- reference copy of the deploy script.
- `.env.example` -- the full set of keys the app needs; never real values.
- `.github/workflows/ci.yml` -- runs before any deploy would happen; CI must be green.

## Custom domains and LetsEncrypt

Automated LetsEncrypt issuance for reseller custom domains (Phase 1) goes through the Ploi API (`PLOI_API_KEY`, `PLOI_SERVER_ID` in `.env`) rather than the dashboard, since it has to run unattended per reseller. Not implemented yet -- tracked in `TODO.md` Phase 1.
