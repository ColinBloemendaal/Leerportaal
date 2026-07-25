#!/usr/bin/env bash
set -euo pipefail

# Reference copy of the deploy script configured in Ploi's dashboard for
# this site (Site -> Deploy Script). Ploi runs this as the site's SSH
# user on every deploy, already `cd`'d into the site's document root with
# the site's chosen PHP/Node versions on PATH. Keep this file and the
# dashboard copy in sync by hand -- Ploi does not read this file directly.
#
# Zero-downtime deployment is a Ploi site setting ("Zero-downtime
# deployment" toggle), not scripted here: when enabled, Ploi builds each
# release in a fresh directory and atomically symlinks it into place once
# this script exits 0, so there is no artisan down/up dance to do.

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

npm ci
npm run build

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
