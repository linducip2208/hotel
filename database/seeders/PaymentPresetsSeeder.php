<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Provider;
use App\Models\ProviderFeatureAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PaymentPresetsSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/payment-presets/payment-presets.json');
        if (! File::exists($path)) {
            $this->command?->warn('File payment-presets.json tidak ditemukan.');
            return;
        }

        $presets = json_decode(File::get($path), true);
        if (! is_array($presets)) return;

        $property = Property::first();
        if (! $property) return;

        foreach ($presets as $preset) {
            $slug = Str::slug($preset['name']);

            $exists = Provider::where('property_id', $property->id)
                ->where('slug', $slug)
                ->where('integration_type', 'payment')
                ->exists();

            if ($exists) continue;

            $provider = Provider::create([
                'property_id' => $property->id,
                'integration_type' => 'payment',
                'name' => $preset['name'],
                'slug' => $slug,
                'api_format' => $preset['api_format'],
                'base_url' => $preset['base_url'],
                'capabilities' => ['supported_methods' => $preset['supported_methods']],
                'extra_config' => ['documentation_url' => $preset['documentation_url'] ?? null],
                'is_active' => false,
                'is_default' => false,
                'display_order' => 10,
                'test_status' => 'untested',
            ]);

            if (! ProviderFeatureAssignment::where('property_id', $property->id)
                ->where('feature', 'booking_payment')
                ->exists()) {
                ProviderFeatureAssignment::create([
                    'property_id' => $property->id,
                    'feature' => 'booking_payment',
                    'provider_id' => $provider->id,
                ]);
            }

            $this->command?->info("Provider '{$preset['name']}' berhasil diimpor.");
        }
    }
}
