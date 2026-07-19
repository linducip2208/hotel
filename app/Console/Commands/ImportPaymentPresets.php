<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\Provider;
use App\Models\ProviderFeatureAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportPaymentPresets extends Command
{
    protected $signature = 'hotel:import-payment-presets {--property= : Property ID} {--activate : Aktifkan provider pertama sebagai default}';
    protected $description = 'Impor 13+ payment gateway presets dari storage/app/payment-presets/payment-presets.json';

    public function handle(): int
    {
        $path = storage_path('app/payment-presets/payment-presets.json');
        if (! File::exists($path)) {
            $this->error('File preset tidak ditemukan: ' . $path);
            return self::FAILURE;
        }

        $presets = json_decode(File::get($path), true);
        if (! is_array($presets)) {
            $this->error('Format JSON tidak valid.');
            return self::FAILURE;
        }

        $propertyId = $this->option('property');
        $property = $propertyId
            ? Property::find($propertyId)
            : Property::first();

        if (! $property) {
            $this->error('Property tidak ditemukan.');
            return self::FAILURE;
        }

        $this->info("Property: {$property->name} (ID: {$property->id})");
        $this->newLine();

        $imported = 0;
        $skipped = 0;

        foreach ($presets as $i => $preset) {
            $slug = Str::slug($preset['name']);

            $exists = Provider::where('property_id', $property->id)
                ->where('slug', $slug)
                ->where('integration_type', 'payment')
                ->exists();

            if ($exists) {
                $this->line("  <fg=gray>Lewati: {$preset['name']} (sudah ada)</>");
                $skipped++;
                continue;
            }

            $provider = Provider::create([
                'property_id' => $property->id,
                'integration_type' => 'payment',
                'name' => $preset['name'],
                'slug' => $slug,
                'api_format' => $preset['api_format'],
                'base_url' => $preset['base_url'],
                'capabilities' => ['supported_methods' => $preset['supported_methods']],
                'extra_config' => ['documentation_url' => $preset['documentation_url'] ?? null],
                'is_active' => $this->option('activate') && $i === 0,
                'is_default' => $this->option('activate') && $i === 0,
                'display_order' => $i + 1,
                'test_status' => 'untested',
            ]);

            if ($this->option('activate') && $i === 0) {
                ProviderFeatureAssignment::updateOrCreate(
                    ['property_id' => $property->id, 'feature' => 'booking_payment'],
                    ['provider_id' => $provider->id],
                );
                $this->line("  <fg=green>Default: {$preset['name']}</>");
            }

            $methods = implode(', ', $preset['supported_methods']);
            $this->line("  <fg=green>Impor:</> {$preset['name']} <fg=gray>({$preset['api_format']} · {$methods})</>");
            $imported++;
        }

        $this->newLine();
        $this->info("Selesai: {$imported} diimpor, {$skipped} dilewati.");
        $this->line("Total provider: <fg=yellow>" . Provider::where('property_id', $property->id)->where('integration_type', 'payment')->count() . "</>");
        $this->line("");
        $this->line("Selanjutnya: isi API Key + Secret di halaman admin, lalu aktifkan provider.");

        return self::SUCCESS;
    }
}
