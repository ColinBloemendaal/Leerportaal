<?php

declare(strict_types=1);

namespace App\Actions\Gdpr;

use App\Contracts\Repositories\ExportRepository;
use App\Models\Reseller;
use App\Services\Gdpr\RetentionPolicy;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

/**
 * CLAUDE.md §8 (GDPR): "Retention policies per data type." An export is
 * a full CSV dump of personal data -- it shouldn't linger on disk (or in
 * the exports table) indefinitely once it's already past its own
 * expires_at and the grace period on top of that has elapsed. Removes
 * both the generated file and the database row.
 */
final readonly class PurgeExpiredExports
{
    public function __construct(
        private ExportRepository $exports,
        private RetentionPolicy $retention,
        private FilesystemFactory $filesystem,
    ) {}

    public function __invoke(?Reseller $reseller): int
    {
        $cutoff = now()->subDays($this->retention->expiredExportsGraceDaysFor($reseller));

        $stale = $this->exports->expiredBefore($cutoff, $reseller?->id);

        foreach ($stale as $export) {
            if ($export->disk !== null && $export->path !== null) {
                $this->filesystem->disk($export->disk)->delete($export->path);
            }

            $export->forceDelete();
        }

        return $stale->count();
    }
}
