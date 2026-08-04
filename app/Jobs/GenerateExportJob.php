<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\Repositories\ExportRepository;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use App\Services\Exporting\ExportResourceRegistry;
use App\Tenancy\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use LogicException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Throwable;

/**
 * Generates one export's file (CSV or XLSX, see Export::format). Reuses
 * each index's existing JsonResource for row-shaping -- the exact same
 * columns the browser page already renders, sourced through
 * ExportResourceRegistry rather than a second, parallel set of column
 * definitions.
 */
final class GenerateExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Every matching row in one file -- exports are a bounded, one-shot
     * admin action, not a paginated browse. High enough that no
     * realistic tenant's data gets truncated, without being unbounded.
     */
    private const MAX_ROWS = 50_000;

    public function __construct(public readonly int $exportId) {}

    public function handle(ExportRepository $exports, ExportResourceRegistry $registry, TenantContext $tenantContext, FilesystemFactory $filesystem): void
    {
        $export = $exports->findById($this->exportId);

        if ($export === null) {
            return;
        }

        $export->update(['status' => ExportStatus::Processing]);

        if ($registry->requiresTenantContext($export->resource_type)) {
            $reseller = $export->reseller;

            if ($reseller === null) {
                throw new LogicException("Export #{$export->id} needs a reseller for tenant-scoped resource {$export->resource_type->value}, but has none.");
            }

            $tenantContext->set($reseller);
        }

        $resourceClass = $registry->resourceClassFor($export->resource_type);
        $filters = FilterRequestData::fromArray($export->filters);
        $items = $registry->itemsFor($export->resource_type, $filters, self::MAX_ROWS);

        $fakeRequest = Request::create('/');

        /** @var list<array<string, mixed>> $rows */
        $rows = [];

        foreach ($items as $model) {
            $shaped = (new $resourceClass($model))->toArray($fakeRequest);

            if (! is_array($shaped)) {
                throw new LogicException($resourceClass.'::toArray() must return an array for CSV export.');
            }

            $rows[] = $shaped;
        }

        $disk = 'local';
        $path = 'exports/'.Str::uuid()->toString().'.'.$export->format->extension();
        $content = $export->format === ExportFormat::Xlsx ? $this->toXlsx($rows) : $this->toCsv($rows);
        $filesystem->disk($disk)->put($path, $content);

        $export->update([
            'status' => ExportStatus::Completed,
            'disk' => $disk,
            'path' => $path,
            'expires_at' => now()->addDay(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        app(ExportRepository::class)->findById($this->exportId)?->update([
            'status' => ExportStatus::Failed,
            'failure_reason' => Str::limit($exception->getMessage(), 250),
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            throw new LogicException('Could not open a temporary stream to build the export CSV.');
        }

        if ($rows !== []) {
            fputcsv($handle, array_keys($rows[0]));
        }

        foreach ($rows as $row) {
            fputcsv($handle, array_map(
                static fn (mixed $value): string => is_scalar($value) ? (string) $value : (string) json_encode($value),
                $row,
            ));
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv === false ? '' : $csv;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function toXlsx(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if ($rows !== []) {
            $sheet->fromArray(array_keys($rows[0]), null, 'A1');

            $sheet->fromArray(array_map(
                static fn (array $row): array => array_map(
                    static fn (mixed $value): string => is_scalar($value) ? (string) $value : (string) json_encode($value),
                    $row,
                ),
                $rows,
            ), null, 'A2');
        }

        // PhpSpreadsheet's writer only writes to a real file path, not a
        // stream -- a temp file round-trip is the simplest way to get the
        // bytes back out to hand to the Filesystem disk.
        $tempPath = tempnam(sys_get_temp_dir(), 'export-xlsx-');

        if ($tempPath === false) {
            throw new LogicException('Could not create a temporary file to build the export XLSX.');
        }

        try {
            (new XlsxWriter($spreadsheet))->save($tempPath);
            $contents = file_get_contents($tempPath);

            return $contents === false ? '' : $contents;
        } finally {
            unlink($tempPath);
        }
    }
}
