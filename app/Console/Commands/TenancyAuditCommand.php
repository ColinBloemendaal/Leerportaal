<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Concerns\TenantScoped;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Consistency check, not a whitelist: a table with reseller_id but a model
 * that doesn't use TenantScoped is the dangerous case (scoping silently
 * missing); a model using TenantScoped whose table lacks reseller_id is
 * broken. Platform-owned models need no explicit exemption -- they simply
 * have no reseller_id column, so neither check applies to them.
 *
 * Deliberate exceptions: tables that mix platform-owned rows
 * (reseller_id null) with reseller-owned rows, so they can't use the
 * fail-closed TenantScoped trait the way single-ownership tables do.
 * Reseller-scoped queries for these are explicit elsewhere instead --
 * User via ad-hoc queries, CourseCategory, Course, and Media via their
 * respective visibleToCurrentReseller() repository methods. See
 * CLAUDE.md §3.
 */
final class TenancyAuditCommand extends Command
{
    /**
     * @var array<int, class-string<Model>>
     */
    private const EXEMPT = [
        'App\Models\User',
        'App\Models\CourseCategory',
        'App\Models\Course',
        'App\Models\Media',
    ];

    /**
     * @var string
     */
    protected $signature = 'tenancy:audit';

    /**
     * @var string
     */
    protected $description = 'Fail if any tenant table lacks reseller_id or the TenantScoped trait';

    public function handle(): int
    {
        $problems = [];

        foreach ($this->modelClasses() as $class) {
            if (in_array($class, self::EXEMPT, true)) {
                continue;
            }

            $table = (new $class)->getTable();

            if (! Schema::hasTable($table)) {
                continue;
            }

            $hasColumn = Schema::hasColumn($table, 'reseller_id');
            $usesTrait = in_array(TenantScoped::class, class_uses_recursive($class), true);

            $problem = self::describeInconsistency($class, $table, $hasColumn, $usesTrait);

            if ($problem !== null) {
                $problems[] = $problem;
            }
        }

        if ($problems === []) {
            $this->info('Tenancy audit passed: every tenant table is consistently scoped.');

            return self::SUCCESS;
        }

        $this->error('Tenancy audit failed:');

        foreach ($problems as $problem) {
            $this->line("  - {$problem}");
        }

        return self::FAILURE;
    }

    /**
     * Pure decision logic, kept separate from schema/reflection lookups so
     * it can be unit tested without touching the database.
     */
    public static function describeInconsistency(string $class, string $table, bool $hasColumn, bool $usesTrait): ?string
    {
        if ($hasColumn && ! $usesTrait) {
            return "{$class}: table '{$table}' has reseller_id but the model does not use TenantScoped.";
        }

        if ($usesTrait && ! $hasColumn) {
            return "{$class}: model uses TenantScoped but table '{$table}' has no reseller_id column.";
        }

        return null;
    }

    /**
     * @return array<int, class-string<Model>>
     */
    private function modelClasses(): array
    {
        $classes = [];

        foreach (File::allFiles(app_path('Models')) as $file) {
            $class = 'App\\Models\\'.str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname(),
            );

            if (class_exists($class) && is_subclass_of($class, Model::class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
