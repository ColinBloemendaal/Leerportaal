<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Reporting;

final readonly class KlantDashboardData
{
    /**
     * @param  list<KlantCursistSummaryData>  $cursisten
     */
    public function __construct(
        public int $cursistCount,
        public array $cursisten,
    ) {}
}
