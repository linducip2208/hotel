@extends('panel.layout')
@section('title', 'Cancellation Policies')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Cancellation Policies</h1>
    <p class="text-sm text-gray-500 mt-0.5">Define penalty rules for booking cancellations</p>
</div>

@if (session('success') || session('warning') || $errors->any())
<div class="mb-4 space-y-2">
    @if (session('success'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>@endif
    @if (session('warning'))<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl text-sm">{{ session('warning') }}</div>@endif
    @foreach ($errors->all() as $err)
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">{{ $err }}</div>
    @endforeach
</div>
@endif

<div class="grid md:grid-cols-3 gap-5" x-data="policyPage()">

    {{-- Policies list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-50">
                @forelse ($policies as $p)
                <div class="px-5 py-4 hover:bg-gray-50/60 transition-colors" x-data="{ editing: false }">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg {{ $p->is_active ? 'bg-primary-50' : 'bg-gray-100' }} flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 {{ $p->is_active ? 'text-primary-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">{{ $p->name }}</span>
                            @if ($p->rate_plans_count > 0)
                                <span class="text-xs text-gray-400" title="Rate plans using this policy">{{ $p->rate_plans_count }} rate plan</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($p->is_refundable)
                            <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Refundable</span>
                            @else
                            <span class="text-xs font-medium bg-red-50 text-red-600 px-2 py-0.5 rounded-full">Non-refundable</span>
                            @endif
                            @if ($p->is_active)
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                            @else
                            <span class="text-xs font-medium bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                            @endif
                        </div>
                    </div>

                    {{-- Preview rules — ordered by days before check-in --}}
                    @if ($p->rules)
                    <div class="ml-10.5 flex flex-wrap gap-1.5 mb-2">
                        @foreach (collect($p->rules)->sortByDesc('days_before') as $r)
                        <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md font-mono">
                            {{ $r['days_before'] }}+ hari → {{ $r['penalty_pct'] }}%
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Row actions --}}
                    <div class="ml-10.5 flex items-center gap-2 text-xs">
                        <button type="button" @click="editing = !editing" class="text-primary-600 hover:text-primary-800 font-medium">Edit</button>
                        <form method="POST" action="{{ route('panel.settings.cancellation-policies.duplicate', $p->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-700 font-medium">Duplicate</button>
                        </form>
                        <form method="POST" action="{{ route('panel.settings.cancellation-policies.toggle', $p->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-700 font-medium">{{ $p->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                        <form method="POST" action="{{ route('panel.settings.cancellation-policies.destroy', $p->id) }}" class="inline"
                              onsubmit="return confirm('Hapus policy ini? Policy yang masih dipakai rate plan hanya akan dinonaktifkan.')">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                        </form>
                    </div>

                    {{-- Inline edit form --}}
                    <div x-show="editing" x-cloak class="mt-3 pt-3 border-t border-gray-100">
                        <form method="POST" action="{{ route('panel.settings.cancellation-policies.update', $p->id) }}" class="space-y-2">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Policy</label>
                                <input type="text" name="name" required maxlength="100" value="{{ $p->name }}"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm outline-none focus:border-primary-400">
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="is_refundable" value="0">
                                <input type="checkbox" name="is_refundable" value="1" {{ $p->is_refundable ? 'checked' : '' }} id="ref_{{ $p->id }}" class="w-4 h-4 rounded border-gray-300 text-primary-600">
                                <label for="ref_{{ $p->id }}" class="text-xs text-gray-700">Refundable</label>
                            </div>
                            @foreach ($p->rules ?? [] as $i => $r)
                                <input type="hidden" name="rules[{{ $i }}][days_before]" value="{{ $r['days_before'] }}">
                                <input type="hidden" name="rules[{{ $i }}][penalty_pct]" value="{{ $r['penalty_pct'] }}">
                            @endforeach
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Guest-Facing Text</label>
                                <textarea name="display_text" rows="2" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm resize-none">{{ $p->display_text }}</textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">Simpan</button>
                                <button type="button" @click="editing = false" class="border border-gray-200 text-gray-600 text-xs font-medium px-3 py-1.5 rounded-lg">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10">
                    <p class="text-sm text-gray-500">No cancellation policies yet. Buat policy pertama di panel kanan.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- New policy form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">New Policy</h2>
        </div>
        <form method="POST" action="{{ route('panel.settings.cancellation-policies.store') }}" class="p-5 space-y-3"
              x-on:submit="submitting = true">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Policy Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required maxlength="100" placeholder="Flexible, Non-refundable…"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>

            <div class="flex items-center gap-2 p-3 bg-gray-50/60 rounded-xl border border-gray-100">
                <input type="hidden" name="is_refundable" value="0">
                <input type="checkbox" name="is_refundable" id="is_refundable_new" value="1" checked
                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-400">
                <label for="is_refundable_new" class="text-xs font-medium text-gray-700">Refundable</label>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold text-gray-600">Penalty Rules</label>
                    <button type="button" @click="rules.push({days_before: 0, penalty_pct: 0})"
                            class="text-xs text-primary-600 hover:text-primary-800 font-medium">+ Add</button>
                </div>
                <div class="space-y-2">
                    <template x-for="(r, i) in rules" :key="i">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-gray-500 shrink-0">≥</span>
                            <input type="number" min="0" :name="'rules['+i+'][days_before]'" x-model.number="r.days_before" required
                                   class="w-16 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 text-sm text-center outline-none focus:border-primary-400 transition-all">
                            <span class="text-gray-500 shrink-0 text-xs">hari →</span>
                            <input type="number" min="0" max="100" step="0.01" :name="'rules['+i+'][penalty_pct]'" x-model.number="r.penalty_pct" required
                                   class="w-16 rounded-lg border border-gray-200 bg-gray-50 px-2 py-1.5 text-sm text-center outline-none focus:border-primary-400 transition-all">
                            <span class="text-gray-500 shrink-0 text-xs">%</span>
                            <button type="button" @click="rules.splice(i, 1)"
                                    class="text-gray-300 hover:text-red-400 transition-colors ml-auto shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Live preview --}}
            <div class="rounded-xl border border-dashed border-gray-200 p-3 bg-gray-50/50">
                <p class="text-xs font-semibold text-gray-600 mb-1.5">Preview kebijakan:</p>
                <template x-for="(r, i) in [...rules].sort((a,b) => b.days_before - a.days_before)" :key="'pv'+i">
                    <p class="text-xs text-gray-500 font-mono" x-text="r.days_before + '+ hari sebelum check-in → ' + r.penalty_pct + '% denda'"></p>
                </template>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Guest-Facing Text</label>
                <textarea name="display_text" rows="3" placeholder="Displayed to guests during booking…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>

            <button type="submit" :disabled="submitting"
                    class="w-full bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                <span x-show="!submitting">Save Policy</span>
                <span x-show="submitting" x-cloak>Menyimpan…</span>
            </button>
        </form>
    </div>

</div>

@endsection

@push('scripts')
<script>
function policyPage() {
    return {
        rules: [{days_before: 14, penalty_pct: 0}, {days_before: 7, penalty_pct: 50}, {days_before: 0, penalty_pct: 100}],
        submitting: false,
    };
}
</script>
@endpush
