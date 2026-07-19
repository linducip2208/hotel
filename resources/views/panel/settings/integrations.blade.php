@extends('panel.layout')
@section('title', 'Provider Lainnya')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Provider Lainnya (BYOK)</h1>
    <p class="text-sm text-gray-500 mt-0.5">SMS, WhatsApp, Email, Storage, Captcha & integrasi non-core. Untuk AI Providers → <a href="{{ route('panel.ai.providers') }}" class="text-indigo-600 font-semibold hover:underline">AI Tools</a> · OTA → <a href="{{ route('panel.channel.providers') }}" class="text-indigo-600 font-semibold hover:underline">Channel Manager</a> · Payment → <a href="{{ route('panel.settings.payments.index') }}" class="text-indigo-600 font-semibold hover:underline">Payment Gateway</a></p>
</div>

@php
    $tabTypes = [
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
        'mail' => 'Email',
        'storage' => 'Storage',
        'captcha' => 'Captcha',
        'accounting_export' => 'Accounting Export',
        'door_lock' => 'Door Lock',
        'rate_shopper' => 'Rate Shopper',
        'other' => 'Lainnya',
    ];
    $currentType = $currentType ?? null;
@endphp

<div class="flex flex-wrap gap-2 mb-6">
    <a href="?" class="px-3 py-1.5 rounded-full text-xs font-semibold {{ $currentType === null || $currentType === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
    @foreach ($tabTypes as $key => $label)
    <a href="?type={{ $key }}" class="px-3 py-1.5 rounded-full text-xs font-semibold {{ $currentType === $key ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- Provider list --}}
@if ($providers->isNotEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Connected Providers</h2>
        <span class="text-xs text-gray-400">{{ $providers->count() }} configured</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Provider</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Format</th>
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
                            @php
                                $typeColors = ['ai' => 'violet', 'payment' => 'emerald', 'sms' => 'blue', 'whatsapp' => 'green', 'mail' => 'amber', 'storage' => 'cyan', 'captcha' => 'rose', 'rate_shopper' => 'indigo'];
                                $tc = $typeColors[$p->integration_type] ?? 'gray';
                            @endphp
                            <div class="w-8 h-8 rounded-lg bg-{{ $tc }}-50 flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-{{ $tc }}-600">{{ strtoupper(substr($p->integration_type, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $p->name }}</div>
                                @if ($p->is_default)
                                <span class="text-xs text-primary-600 font-medium">Default</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2 py-0.5 rounded-full capitalize">{{ str_replace('_', ' ', $p->integration_type) }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-500 font-mono">{{ $p->api_format }}</td>
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
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $ts === 'ok' ? 'bg-emerald-50 text-emerald-700' : ($ts === 'fail' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500') }}">
                            {{ $ts }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-2 justify-end">
                            <form method="POST" action="{{ route('panel.settings.integrations.test', $p->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-primary-600 hover:text-primary-800 bg-primary-50 hover:bg-primary-100 px-2.5 py-1 rounded-lg transition-colors">Test</button>
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
@endif

{{-- Add provider form --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 max-w-2xl">
    <div class="px-5 py-4">
        <h2 class="text-sm font-semibold text-gray-700">Add Provider</h2>
        <p class="text-xs text-gray-400 mt-0.5">Format-based generic adapters — works with any compatible API endpoint</p>
    </div>
    <div class="p-5">
        <form method="POST" action="{{ route('panel.settings.integrations.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Integration Type <span class="text-red-500">*</span></label>
                    <select name="integration_type" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                        <option value="sms">SMS</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="mail">Mail / SMTP</option>
                        <option value="storage">Object Storage</option>
                        <option value="captcha">Captcha</option>
                        <option value="accounting_export">Accounting Export</option>
                        <option value="door_lock">Door Lock</option>
                        <option value="rate_shopper">Rate Shopper</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">API Format <span class="text-red-500">*</span></label>
                    <input type="text" name="api_format" value="{{ old('api_format') }}" required
                           placeholder="e.g. openai_compatible"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Display Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="My LLM Production"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Base URL</label>
                    <input type="url" name="base_url" value="{{ old('base_url') }}" placeholder="https://api.example.com/v1"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">API Key</label>
                    <input type="text" name="api_key" value="{{ old('api_key') }}" placeholder="sk-… (stored encrypted)"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Secret / Token</label>
                    <input type="text" name="secret" value="{{ old('secret') }}" placeholder="optional"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Default Model (AI)</label>
                    <input type="text" name="default_model" value="{{ old('default_model') }}" placeholder="e.g. gpt-4o-mini"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" id="is_default_chk" class="rounded text-primary-600 border-gray-300 focus:ring-primary-400">
                <label for="is_default_chk" class="text-sm text-gray-600">Set as default for this integration type</label>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Provider
            </button>
        </form>
    </div>
</div>

@endsection
