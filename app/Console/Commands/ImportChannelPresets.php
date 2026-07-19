<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\Property;
use App\Models\Provider;
use App\Models\ProviderFeatureAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportChannelPresets extends Command
{
    protected $signature = 'hotel:import-channel-presets {--property= : Property ID} {--activate : Aktifkan channel pertama sebagai default}';
    protected $description = 'Impor 10 OTA channel presets dari storage/app/channel-presets/channel-presets.json';

    public function handle(): int
    {
        $path = storage_path('app/channel-presets/channel-presets.json');
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

        $importedProviders = 0;
        $skippedProviders = 0;
        $importedChannels = 0;
        $skippedChannels = 0;

        foreach ($presets as $i => $preset) {
            $slug = Str::slug($preset['name']);

            $provider = Provider::where('property_id', $property->id)
                ->where('slug', $slug)
                ->where('integration_type', 'ota')
                ->first();

            if (! $provider) {
                $extraConfig = array_filter([
                    'documentation_url' => $preset['documentation_url'] ?? null,
                    'supports_ari_push' => $preset['supports_ari_push'] ?? false,
                    'supports_booking_pull' => $preset['supports_booking_pull'] ?? false,
                    'auth_type' => $preset['auth_type'] ?? null,
                ]);

                if (! empty($preset['oauth_base_url'])) {
                    $extraConfig['oauth_base_url'] = $preset['oauth_base_url'];
                }

                $provider = Provider::create([
                    'property_id' => $property->id,
                    'integration_type' => 'ota',
                    'name' => $preset['name'],
                    'slug' => $slug,
                    'api_format' => $preset['api_format'],
                    'base_url' => $preset['base_url'],
                    'extra_config' => $extraConfig,
                    'capabilities' => [
                        'fields' => $preset['fields'] ?? [],
                    ],
                    'is_active' => $this->option('activate') && $i === 0,
                    'is_default' => $this->option('activate') && $i === 0,
                    'display_order' => $i + 1,
                    'test_status' => 'untested',
                ]);

                $this->line("  <fg=green>Provider baru:</> {$preset['name']} <fg=gray>({$preset['api_format']})</>");
                $importedProviders++;
            } else {
                $this->line("  <fg=gray>Provider sudah ada: {$preset['name']}</>");
                $skippedProviders++;
            }

            $channelExists = Channel::where('property_id', $property->id)
                ->where('code', $preset['api_format'])
                ->exists();

            if (! $channelExists) {
                Channel::create([
                    'property_id' => $property->id,
                    'provider_id' => $provider->id,
                    'code' => $preset['api_format'],
                    'api_format' => $preset['api_format'],
                    'name' => $preset['name'],
                    'adapter_class' => config("integrations.channel_adapters.{$preset['api_format']}", ''),
                    'config' => array_filter([
                        'base_url' => $preset['base_url'],
                        'auth_type' => $preset['auth_type'] ?? null,
                    ]),
                    'is_active' => false,
                    'two_way_sync' => true,
                ]);

                $this->line("  <fg=green>Channel baru:</> {$preset['name']}");
                $importedChannels++;
            } else {
                $this->line("  <fg=gray>Channel sudah ada: {$preset['name']}</>");
                $skippedChannels++;
            }

            $this->newLine();
        }

        $this->info("Selesai!");
        $this->line("  Provider: {$importedProviders} diimpor, {$skippedProviders} dilewati.");
        $this->line("  Channel:  {$importedChannels} diimpor, {$skippedChannels} dilewati.");
        $this->line("");
        $this->line("Selanjutnya: isi API Key + Secret di halaman admin, lalu aktifkan channel.");
        $this->line("Jalankan <fg=yellow>php artisan migrate</> terlebih dahulu jika belum.");

        return self::SUCCESS;
    }
}
