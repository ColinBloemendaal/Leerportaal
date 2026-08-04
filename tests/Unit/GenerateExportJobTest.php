<?php

declare(strict_types=1);

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\ExportStatus;
use App\Enums\FilterableResource;
use App\Jobs\GenerateExportJob;
use App\Models\Export;
use App\Models\Invoice;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('local');
});

it('generates a CSV for a platform-wide resource with no reseller context needed', function (): void {
    Reseller::factory()->create(['name' => 'Acme']);
    $user = User::factory()->create();
    $export = Export::factory()->create([
        'user_id' => $user->id,
        'resource_type' => FilterableResource::Resellers,
        'filters' => (new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: []))->filters,
    ]);

    app()->call([new GenerateExportJob($export->id), 'handle']);

    $export->refresh();

    expect($export->status)->toBe(ExportStatus::Completed)
        ->and($export->disk)->toBe('local')
        ->and($export->path)->not->toBeNull()
        ->and($export->expires_at)->not->toBeNull();

    Storage::disk('local')->assertExists($export->path);
    expect(Storage::disk('local')->get($export->path))->toContain('Acme');
});

it('sets the tenant context from the export\'s reseller for a tenant-scoped resource', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    ResellerKlant::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Own Klant']);
    ResellerKlant::factory()->create(['reseller_id' => $otherReseller->id, 'name' => 'Other Klant']);

    $user = User::factory()->create();
    $export = Export::factory()->create([
        'user_id' => $user->id,
        'reseller_id' => $reseller->id,
        'resource_type' => FilterableResource::Klanten,
        'filters' => [],
    ]);

    app()->call([new GenerateExportJob($export->id), 'handle']);

    $export->refresh();

    expect($export->status)->toBe(ExportStatus::Completed);

    $csv = Storage::disk('local')->get($export->path);

    expect($csv)->toContain('Own Klant')
        ->and($csv)->not->toContain('Other Klant');
});

it('exports a reseller\'s own invoices, using the tenant context from the export', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    Invoice::factory()->for($reseller)->issued()->create(['total_cents' => 4500]);
    Invoice::factory()->for($otherReseller)->issued()->create(['total_cents' => 9900]);

    $user = User::factory()->create();
    $export = Export::factory()->create([
        'user_id' => $user->id,
        'reseller_id' => $reseller->id,
        'resource_type' => FilterableResource::Invoices,
        'filters' => [],
    ]);

    app()->call([new GenerateExportJob($export->id), 'handle']);

    $export->refresh();
    expect($export->status)->toBe(ExportStatus::Completed);

    $csv = Storage::disk('local')->get($export->path);

    expect($csv)->toContain('4500')
        ->and($csv)->not->toContain('9900');
});

it('generates a real, readable XLSX file when the format is xlsx', function (): void {
    Reseller::factory()->create(['name' => 'Acme']);
    $user = User::factory()->create();
    $export = Export::factory()->xlsx()->create([
        'user_id' => $user->id,
        'resource_type' => FilterableResource::Resellers,
        'filters' => (new FilterRequestData(search: null, sort: null, sortDirection: 'asc', filters: []))->filters,
    ]);

    app()->call([new GenerateExportJob($export->id), 'handle']);

    $export->refresh();

    expect($export->status)->toBe(ExportStatus::Completed)
        ->and($export->path)->toEndWith('.xlsx');

    $contents = Storage::disk('local')->get($export->path);
    expect($contents)->not->toBeNull();

    $tempPath = tempnam(sys_get_temp_dir(), 'xlsx-read-test-');
    file_put_contents($tempPath, $contents);

    $spreadsheet = (new XlsxReader)->load($tempPath);
    $sheetData = $spreadsheet->getActiveSheet()->toArray();

    unlink($tempPath);

    expect(collect($sheetData)->flatten()->all())->toContain('Acme');
});

it('marks the export as failed via the job\'s failed() hook', function (): void {
    $user = User::factory()->create();
    $export = Export::factory()->create(['user_id' => $user->id]);

    (new GenerateExportJob($export->id))->failed(new RuntimeException('Disk unavailable'));

    $export->refresh();

    expect($export->status)->toBe(ExportStatus::Failed)
        ->and($export->failure_reason)->toBe('Disk unavailable');
});
