@extends('panel.layout')
@section('title', 'Pengaturan Printer')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.dashboard') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Printer Thermal</h1>
        <p class="text-sm text-gray-500 mt-0.5">Konfigurasi printer ESC/POS untuk cetak struk dan kitchen order</p>
    </div>
</div>

@if (session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 4000)"
     class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('success') }}
    <button @click="show=false" class="ml-auto text-emerald-500 hover:text-emerald-700">&times;</button>
</div>
@endif

<form method="POST" action="{{ route('panel.settings.printer.update') }}" class="space-y-5 max-w-2xl">
    @csrf
    @method('PATCH')

    {{-- Printer Utama (Folio) --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Printer Utama (Struk Folio & POS)
            </h2>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Printer</label>
                <input type="text" name="thermal_printer_name" value="{{ old('thermal_printer_name', $printerName) }}" placeholder="Contoh: TM-T88"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis Koneksi</label>
                <select name="thermal_printer_interface"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                    <option value="network" @selected(old('thermal_printer_interface', $printerInterface) === 'network')>Network (Ethernet)</option>
                    <option value="usb" @selected(old('thermal_printer_interface', $printerInterface) === 'usb')>USB</option>
                    <option value="serial" @selected(old('thermal_printer_interface', $printerInterface) === 'serial')>Serial (COM)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">IP / Hostname</label>
                    <input type="text" name="thermal_printer_host" value="{{ old('thermal_printer_host', $printerHost) }}" placeholder="192.168.1.100"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Port</label>
                    <input type="number" name="thermal_printer_port" value="{{ old('thermal_printer_port', $printerPort) }}" placeholder="9100" min="1" max="65535"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
        </div>
    </div>

    {{-- Kitchen Printer --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Printer Dapur (Kitchen Display / Order)
            </h2>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">IP / Hostname</label>
                    <input type="text" name="thermal_kitchen_printer_host" value="{{ old('thermal_kitchen_printer_host', $kitchenPrinterHost) }}" placeholder="192.168.1.101"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Port</label>
                    <input type="number" name="thermal_kitchen_printer_port" value="{{ old('thermal_kitchen_printer_port', $kitchenPrinterPort) }}" placeholder="9100" min="1" max="65535"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 pt-1">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Simpan Pengaturan
        </button>

        <form method="POST" action="{{ route('panel.print.test') }}" class="inline">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-800 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Test Print
            </button>
        </form>

        <a href="{{ route('panel.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</a>
    </div>
</form>

@endsection
