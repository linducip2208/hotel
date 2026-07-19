@extends('panel.layout')
@section('title', isset($report) ? 'Edit Laporan' : 'Buat Laporan')
@section('content')

@php
    $catLabels = ['revenue' => 'Revenue', 'operations' => 'Operasional', 'guests' => 'Tamu', 'finance' => 'Keuangan'];
    $selectedWidgets = isset($report) ? ($report->widgets ?? []) : [];
    $isEdit = isset($report);
@endphp

<div class="mb-6">
    <a href="{{ route('panel.reports.custom-reports.index') }}" class="text-xs text-primary-600 hover:underline mb-1 inline-block">&larr; Kembali</a>
    <h1 class="text-2xl font-bold text-gray-900">{{ $isEdit ? 'Edit Laporan' : 'Buat Laporan Baru' }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">Pilih widget untuk laporan kustom Anda</p>
</div>

@if ($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl px-4 py-3 mb-5 text-sm">
    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('panel.reports.custom-reports.update', $report->id) : route('panel.reports.custom-reports.store') }}"
      x-data="{
          name: '{{ old('name', $report->name ?? '') }}',
          category: '{{ old('category', $report->category ?? 'revenue') }}',
          isPublic: {{ old('is_public', $report->is_public ?? false) ? 'true' : 'false' }},
          selectedWidgets: {{ json_encode($selectedWidgets) }},
          toggleWidget(key) {
              const idx = this.selectedWidgets.indexOf(key);
              if (idx > -1) this.selectedWidgets.splice(idx, 1);
              else this.selectedWidgets.push(key);
          },
          removeWidget(key) {
              this.selectedWidgets = this.selectedWidgets.filter(w => w !== key);
          }
      }">

    @if($isEdit) @method('PUT') @endif
    @csrf

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Report Config --}}
        <div class="space-y-5">
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-semibold text-gray-700">Konfigurasi</h2>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Laporan <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all" placeholder="Laporan Bulanan Saya">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" x-model="category" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                            <option value="revenue">Revenue</option>
                            <option value="operations">Operasional</option>
                            <option value="guests">Tamu</option>
                            <option value="finance">Keuangan</option>
                        </select>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="is_public" value="1" x-model="isPublic" class="rounded">
                            Tampilkan ke semua user
                        </label>
                    </div>
                    <div class="pt-2">
                        <p class="text-xs text-gray-400 mb-2"><span x-text="selectedWidgets.length"></span> widget dipilih</p>
                    </div>
                </div>
                <div class="px-5 py-4 bg-gray-50/50">
                    <button type="submit" :disabled="selectedWidgets.length === 0 || !name"
                            class="w-full bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                        {{ $isEdit ? 'Perbarui Laporan' : 'Simpan Laporan' }}
                    </button>
                </div>
            </div>

            {{-- Hidden inputs for widgets --}}
            <template x-for="w in selectedWidgets" :key="w">
                <input type="hidden" name="widgets[]" :value="w">
            </template>
        </div>

        {{-- Widget Picker --}}
        <div class="lg:col-span-2 space-y-5">
            @foreach(['revenue', 'operations', 'guests', 'finance'] as $cat)
                @php $catWidgets = array_filter($widgets, fn($w) => $w['category'] === $cat); @endphp
                @if(!empty($catWidgets))
                <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="text-sm font-semibold text-gray-700">{{ $catLabels[$cat] }}</h3>
                    </div>
                    <div class="p-4 grid sm:grid-cols-2 gap-3">
                        @foreach($catWidgets as $key => $w)
                        <div @click="toggleWidget('{{ $key }}')"
                             :class="selectedWidgets.includes('{{ $key }}') ? 'ring-2 ring-primary-400 bg-primary-50/50 border-primary-300' : 'border-gray-200 hover:border-primary-200 hover:bg-gray-50/60'"
                             class="border rounded-xl p-3 cursor-pointer transition-all select-none">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ $w['name'] }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $w['type'] === 'line_chart' ? 'Line Chart' : ($w['type'] === 'bar_chart' ? 'Bar Chart' : ($w['type'] === 'doughnut_chart' || $w['type'] === 'pie_chart' ? 'Donut/Pie' : ($w['type'] === 'table' ? 'Tabel' : 'Stat Card'))) }}</div>
                                </div>
                                <div x-show="selectedWidgets.includes('{{ $key }}')" class="w-5 h-5 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold">&#10003;</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach

            {{-- Preview --}}
            <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden" x-show="selectedWidgets.length > 0">
                <div class="px-5 py-3 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Widget Dipilih</h3>
                    <span class="text-xs text-gray-400" x-text="selectedWidgets.length + ' widget'"></span>
                </div>
                <div class="p-4">
                    <template x-if="selectedWidgets.length === 0">
                        <p class="text-sm text-gray-400 text-center py-6">Klik widget di atas untuk menambahkan ke laporan ini.</p>
                    </template>
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="w in selectedWidgets" :key="w">
                            <div class="border border-dashed border-gray-200 rounded-xl p-3 flex items-center justify-between">
                                <div class="text-sm text-gray-700 font-medium" x-text="w"></div>
                                <button type="button" @click.stop="removeWidget(w)" class="text-red-400 hover:text-red-600 text-lg">&times;</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection
