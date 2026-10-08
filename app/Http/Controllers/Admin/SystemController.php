<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SystemController extends Controller
{
    protected string $flagPath = 'feature-flags.json';

    public function flags()
    {
        return view('admin.system.flags', [
            'features' => config('hotel.features'),
            'overrides' => $this->overrides(),
        ]);
    }

    public function updateFlags(Request $request)
    {
        $data = $request->validate([
            'flags' => 'required|array',
            'flags.*' => 'required|boolean',
        ]);

        // Persist overrides; merged over config defaults at boot.
        $flags = collect($data['flags'])->map(fn ($v) => (bool) $v)->all();
        Storage::disk('local')->put($this->flagPath, json_encode($flags, JSON_PRETTY_PRINT));

        AuditLog::create([
            'property_id' => null,
            'user_type' => 'admin',
            'user_id' => auth('admin')->id(),
            'action' => 'system.feature_flags_updated',
            'metadata' => ['flags' => $flags],
            'ip' => $request->ip(),
        ]);

        return back()->with('status', 'Feature flags tersimpan.');
    }

    public function plans()
    {
        return view('admin.system.plans', ['plans' => Plan::orderBy('price_idr')->get()]);
    }

    public function storePlan(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:plans,slug',
            'price_idr' => 'required|integer|min:0',
            'max_rooms' => 'nullable|integer|min:1',
            'max_users' => 'nullable|integer|min:1',
            'max_properties' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        Plan::create($data + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('status', 'Plan dibuat.');
    }

    public function emailTemplates()
    {
        // Enumerate real blade mail templates on disk — an inventory, not a stub.
        $mailDir = resource_path('views/mail');
        $templates = is_dir($mailDir)
            ? collect(File::allFiles($mailDir))
                ->map(fn ($f) => str_replace(['.blade.php', '/', '\\'], ['', '.', '.'], $f->getRelativePathname()))
                ->values()
            : collect();

        return view('admin.system.email-templates', ['templates' => $templates]);
    }

    public function auditLog()
    {
        return view('admin.system.audit-log', ['logs' => AuditLog::latest()->paginate(100)]);
    }

    /** @return array<string, bool> runtime overrides persisted by updateFlags() */
    public static function overrides(): array
    {
        $path = storage_path('app/private/feature-flags.json');
        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
