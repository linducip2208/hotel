@extends('panel.layout')
@section('title', $campaign ? 'Edit Drip Campaign' : 'Buat Drip Campaign')
@section('content')

@php
$triggerOptions = [
    'booking_confirmed' => 'Booking Dikonfirmasi',
    'checkin' => 'Check-in',
    'checkout' => 'Check-out',
    'post_stay' => 'Pasca Menginap',
    'birthday' => 'Ulang Tahun',
    'inactive' => 'Tamu Non-aktif',
];
@endphp

<div class="mb-6">
    <a href="{{ route('panel.marketing.drip-campaigns.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900">{{ $campaign ? 'Edit Campaign' : 'Buat Campaign Baru' }}</h1>
</div>

<form method="POST" action="{{ $campaign ? route('panel.marketing.drip-campaigns.update', $campaign->id) : route('panel.marketing.drip-campaigns.store') }}"
      x-data="dripForm({{ $campaign ? json_encode($campaign->steps->toArray()) : '[]' }})">
    @csrf
    @if($campaign) @method('PUT') @endif

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Campaign</label>
            <input type="text" name="name" value="{{ old('name', $campaign?->name) }}" required
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Pre-arrival welcome series">
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Trigger Event</label>
            <select name="trigger_event" required
                    class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- Pilih Trigger --</option>
                @foreach($triggerOptions as $val => $label)
                <option value="{{ $val }}" {{ old('trigger_event', $campaign?->trigger_event) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Kapan campaign ini dimulai</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 mb-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700">Langkah (Steps)</h2>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $campaign?->is_active ?? true) ? 'checked' : '' }}>
                Campaign Aktif
            </label>
        </div>

        <template x-for="(step, index) in steps" :key="index">
            <div class="border border-gray-100 rounded-xl p-4 mb-3 bg-gray-50/50">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500" x-text="'Step '+(index+1)"></span>
                    <button type="button" @click="steps.splice(index, 1)"
                            class="text-gray-400 hover:text-rose-600 text-xs p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                <div class="grid md:grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Delay (jam)</label>
                        <input type="number" :name="'steps['+index+'][delay_hours]'" x-model="step.delay_hours" min="0" required
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Channel</label>
                        <select :name="'steps['+index+'][channel]'" x-model="step.channel"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                            <option value="both">WhatsApp + Email</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Template Key</label>
                        <input type="text" :name="'steps['+index+'][template_key]'" x-model="step.template_key"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Subject (untuk email)</label>
                    <input type="text" :name="'steps['+index+'][subject]'" x-model="step.subject"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pesan</label>
                    <textarea :name="'steps['+index+'][message]'" x-model="step.message" rows="3" required
                              class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                              placeholder="Gunakan {guest_name}, {check_in}, {property_name}..."></textarea>
                </div>
            </div>
        </template>

        <button type="button" @click="addStep()"
                class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium px-3 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Step
        </button>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ $campaign ? 'Update Campaign' : 'Simpan Campaign' }}
        </button>
        <a href="{{ route('panel.marketing.drip-campaigns.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
    </div>
</form>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dripForm', (initialSteps) => ({
        steps: initialSteps.length > 0 ? initialSteps.map((s, i) => ({
            delay_hours: s.delay_hours || 0,
            channel: s.channel || 'whatsapp',
            template_key: s.template_key || '',
            subject: s.subject || '',
            message: s.message || '',
        })) : [{
            delay_hours: 0,
            channel: 'whatsapp',
            template_key: '',
            subject: '',
            message: '',
        }],
        addStep() {
            this.steps.push({
                delay_hours: 24,
                channel: 'whatsapp',
                template_key: '',
                subject: '',
                message: '',
            });
        },
    }));
});
</script>
@endsection
