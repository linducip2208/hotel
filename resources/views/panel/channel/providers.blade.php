@extends('panel.layout')
@section('title', 'Provider OTA')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Provider OTA</h1>
    <p class="text-sm text-gray-500 mt-0.5">Konfigurasi koneksi ke Online Travel Agent: Booking.com, Agoda, Expedia, Traveloka, Tiket.com, dan lainnya.</p>
</div>

@if ($providers->isNotEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">OTA Providers Terhubung</h2>
        <span class="text-xs text-gray-400">{{ $providers->count() }} configured</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Provider</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Format</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Base URL</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Test</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($providers as $p)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-cyan-600">{{ strtoupper(substr($p->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $p->name }}</div>
                                @if ($p->is_default)
                                <span class="text-xs text-cyan-600 font-medium">Default</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-500 font-mono">{{ $p->api_format }}</td>
                    <td class="px-4 py-3.5 text-xs text-gray-500 font-mono truncate max-w-[180px]">{{ $p->base_url ?: '—' }}</td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($p->is_active)
                        <span class="inline-flex items-center gap-1 text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @php $ts = $p->test_status ?? 'untested'; @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $ts === 'ok' ? 'bg-emerald-50 text-emerald-700' : ($ts === 'failed' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500') }}">
                            {{ $ts }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-2 justify-end">
                            <form method="POST" action="{{ route('panel.settings.integrations.test', $p->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-cyan-600 hover:text-cyan-800 bg-cyan-50 hover:bg-cyan-100 px-2.5 py-1 rounded-lg transition-colors">Test</button>
                            </form>
                            <form method="POST" action="{{ route('panel.settings.integrations.destroy', $p->id) }}" class="inline"
                                  onsubmit="return confirm('Delete {{ $p->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded-lg transition-colors">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 text-sm text-amber-800 mb-6">
    Belum ada OTA provider yang dikonfigurasi. Tambahkan Booking.com, Agoda, Expedia, dll di bawah.
</div>
@endif

<div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 max-w-2xl">
    <div class="px-5 py-4">
        <h2 class="text-sm font-semibold text-gray-700">Tambah OTA Provider</h2>
        <p class="text-xs text-gray-400 mt-0.5">Hubungkan hotel-mu ke channel eksternal untuk sinkronisasi tarif, ketersediaan, dan reservasi.</p>
    </div>
    <div class="p-5">
        <form method="POST" action="{{ route('panel.settings.integrations.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="integration_type" value="ota">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">API Format <span class="text-red-500">*</span></label>
                    <select name="api_format" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all outline-none">
                        <option value="booking_com">Booking.com (ARI Push)</option>
                        <option value="agoda">Agoda (YCS)</option>
                        <option value="expedia">Expedia (QuickConnect)</option>
                        <option value="traveloka">Traveloka</option>
                        <option value="tiket_com">Tiket.com</option>
                        <option value="site_minder">SiteMinder (Channel Manager)</option>
                        <option value="d_edge">D-EDGE (Channel Manager)</option>
                        <option value="generic_ari">Generic ARI Push</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Display Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Booking.com"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Base URL</label>
                    <input type="url" name="base_url" value="{{ old('base_url') }}" placeholder="https://supply-xml.booking.com/hotels/ota"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">API Key / Hotel ID</label>
                    <input type="text" name="api_key" value="{{ old('api_key') }}" placeholder="Hotel ID (stored encrypted)"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Secret / Password</label>
                    <input type="text" name="secret" value="{{ old('secret') }}" placeholder="OTA secret (encrypted)"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Username (optional)</label>
                    <input type="text" name="extra_config[username]" value="{{ old('extra_config.username') }}" placeholder="OTA username"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all outline-none">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" id="is_default_ota" class="rounded text-cyan-600 border-gray-300 focus:ring-cyan-400">
                <label for="is_default_ota" class="text-sm text-gray-600">Jadikan OTA provider default</label>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah OTA Provider
            </button>
        </form>
    </div>
</div>

@endsection
