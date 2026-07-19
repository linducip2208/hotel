<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderFeatureAssignment;
use App\Services\Integrations\AdapterFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', null);
        $query = Provider::where('property_id', app('current_property')->id);

        if ($type && $type !== 'all') {
            $query->where('integration_type', $type);
        }

        $providers = $query->orderBy('integration_type')->orderBy('display_order')->get();
        $currentType = $type;

        return view('panel.settings.integrations', compact('providers', 'currentType'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'integration_type' => 'required|in:ai,payment,sms,whatsapp,mail,storage,captcha,door_lock,rate_shopper,ota,accounting_export,other',
            'name' => 'required|string|max:255',
            'api_format' => 'required|string|max:64',
            'base_url' => 'nullable|url',
            'api_key' => 'nullable|string',
            'secret' => 'nullable|string',
            'default_model' => 'nullable|string',
            'extra_config' => 'nullable|array',
            'extra_headers' => 'nullable|array',
            'is_default' => 'nullable|boolean',
        ]);

        $provider = new Provider([
            'property_id' => app('current_property')->id,
            'integration_type' => $data['integration_type'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'api_format' => $data['api_format'],
            'base_url' => $data['base_url'] ?? null,
            'default_model' => $data['default_model'] ?? null,
            'extra_config' => $data['extra_config'] ?? null,
            'extra_headers' => $data['extra_headers'] ?? null,
            'is_active' => true,
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);
        if (! empty($data['api_key'])) $provider->setApiKey($data['api_key']);
        if (! empty($data['secret'])) $provider->setSecret($data['secret']);
        $provider->save();

        return back()->with('status', 'Integration created.');
    }

    public function update(Request $request, int $id)
    {
        $provider = Provider::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'base_url' => 'nullable|url',
            'api_key' => 'nullable|string',
            'secret' => 'nullable|string',
            'default_model' => 'nullable|string',
            'extra_config' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);
        $provider->fill($data);
        if (array_key_exists('api_key', $data) && $data['api_key']) $provider->setApiKey($data['api_key']);
        if (array_key_exists('secret', $data) && $data['secret']) $provider->setSecret($data['secret']);
        $provider->save();
        return back();
    }

    public function test(int $id, AdapterFactory $factory)
    {
        $provider = Provider::where('property_id', app('current_property')->id)->findOrFail($id);
        try {
            $adapter = $factory->make($provider);
            $result = $adapter->test();
            $provider->update([
                'test_status' => $result['ok'] ? 'ok' : 'failed',
                'last_tested_at' => now(),
                'test_message' => $result['message'] ?? null,
            ]);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        Provider::where('property_id', app('current_property')->id)->findOrFail($id)->delete();
        return back();
    }

    public function payments()
    {
        $providers = Provider::where('integration_type', 'payment')
            ->where('property_id', app('current_property')->id)
            ->orderBy('display_order')
            ->get();

        $presets = json_decode(file_get_contents(storage_path('app/payment-presets/payment-presets.json')), true);

        return view('panel.settings.payments', compact('providers', 'presets'));
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'provider_name' => 'required|string',
            'api_format' => 'required|string',
            'base_url' => 'nullable|string',
            'custom_base_url' => 'nullable|string',
            'fields' => 'required|array',
        ]);

        $presets = json_decode(file_get_contents(storage_path('app/payment-presets/payment-presets.json')), true);
        $preset = collect($presets)->firstWhere('name', $request->provider_name);

        if (!$preset) {
            return back()->with('error', 'Provider tidak ditemukan.');
        }

        $credentials = [];
        foreach ($request->fields as $key => $value) {
            if ($key === 'is_production') {
                continue;
            }
            if (!empty($value)) {
                $credentials[$key] = Crypt::encryptString($value);
            }
        }

        $isProduction = $request->boolean('fields.is_production');
        $baseUrl = $request->custom_base_url ?: ($isProduction ? $preset['base_url'] : ($preset['sandbox_url'] ?? $preset['base_url']));

        $slug = Str::slug($request->provider_name) . '-' . Str::random(4);

        $provider = Provider::create([
            'property_id' => app('current_property')->id,
            'integration_type' => 'payment',
            'name' => $request->provider_name,
            'slug' => $slug,
            'api_format' => $request->api_format,
            'base_url' => $baseUrl,
            'api_key_encrypted' => json_encode($credentials) ? Crypt::encryptString(json_encode($credentials)) : null,
            'extra_config' => [
                'supported_methods' => $preset['supported_methods'] ?? [],
                'documentation_url' => $preset['documentation_url'] ?? null,
                'is_production' => $isProduction,
            ],
            'capabilities' => ['supported_methods' => $preset['supported_methods'] ?? []],
            'is_active' => true,
            'display_order' => Provider::where('property_id', app('current_property')->id)->where('integration_type', 'payment')->count() + 1,
            'test_status' => 'untested',
        ]);

        ProviderFeatureAssignment::firstOrCreate([
            'property_id' => app('current_property')->id,
            'feature' => 'booking_payment',
        ], [
            'provider_id' => $provider->id,
        ]);

        return redirect()->route('panel.settings.payments.index')->with('status', $request->provider_name . ' berhasil dikonfigurasi.');
    }

    public function togglePayment(int $id)
    {
        $provider = Provider::where('property_id', app('current_property')->id)->findOrFail($id);
        $provider->update(['is_active' => !$provider->is_active]);
        return back()->with('status', $provider->name . ' ' . ($provider->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.');
    }

    public function destroyPayment(int $id)
    {
        $provider = Provider::where('property_id', app('current_property')->id)->findOrFail($id);
        ProviderFeatureAssignment::where('provider_id', $provider->id)->delete();
        $provider->delete();
        return back()->with('status', 'Provider dihapus.');
    }
}
