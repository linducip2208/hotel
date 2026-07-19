<?php

namespace App\Console\Commands\Accounting;

use App\Models\Property;
use App\Services\Accounting\Export\AccountingExporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportDailyCommand extends Command
{
    protected $signature = 'accounting:export-daily {--format=csv : csv|jurnal|accurate}';
    protected $description = 'Export daily journal to CSV/Jurnal/Accurate format';

    public function handle(AccountingExporter $exporter): int
    {
        $year = (int) now()->format('Y');
        $month = (int) now()->format('m');
        $format = $this->option('format');

        Property::all()->each(function (Property $p) use ($exporter, $year, $month, $format) {
            $content = match ($format) {
                'jurnal' => $exporter->jurnalExport($p, $year, $month),
                'accurate' => $exporter->accurateExport($p, $year, $month),
                default => $exporter->csvExport($p, $year, $month),
            };
            $path = "exports/accounting/{$p->slug}/{$year}-".str_pad($month, 2, '0', STR_PAD_LEFT)."-{$format}.csv";
            Storage::disk('local')->put($path, $content);
            $this->info("Exported {$p->name}: {$path}");
        });

        return self::SUCCESS;
    }
}
