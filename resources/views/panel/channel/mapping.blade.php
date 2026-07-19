@extends('panel.layout')
@section('title', 'Room Mapping')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.channel.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Room Mapping</h1>
        <p class="text-sm text-gray-500 mt-0.5">Petakan tipe kamar internal ke kategori channel OTA</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Form —--}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 sticky top-24">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Tambah Mapping</h2>
            <form method="POST" action="{{ route('panel.channel.mapping.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Channel</label>
                        <select name="channel_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Pilih Channel</option>
                            @foreach($channels as $ch)
                            <option value="{{ $ch->id }}">{{ $ch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Tipe Kamar Internal</label>
                        <select name="room_type_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Pilih Tipe Kamar</option>
                            @foreach($roomTypes as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Rate Plan</label>
                        <select name="rate_plan_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Pilih Rate Plan</option>
                            @foreach($ratePlans as $rp)
                            <option value="{{ $rp->id }}">{{ $rp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">ID Kamar di Channel</label>
                        <input type="text" name="channel_room_id" placeholder="Contoh: 12345678"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">ID Rate di Channel</label>
                        <input type="text" name="channel_rate_id" placeholder="Contoh: 987654321"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table —--}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            @if($mappings->isEmpty())
            <div class="flex flex-col items-center justify-center py-20">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <p class="text-base font-semibold text-gray-700">Belum ada mapping</p>
                <p class="text-sm text-gray-400 mt-1.5 text-center max-w-sm">Tambah mapping kamar dari form di samping untuk mulai menghubungkan channel OTA dengan tipe kamar Anda.</p>
            </div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Channel</th>
                        <th class="px-5 py-3">Tipe Kamar</th>
                        <th class="px-5 py-3">Rate Plan</th>
                        <th class="px-5 py-3">ID Channel</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($mappings as $mapping)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $mapping->channel?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $mapping->roomType?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $mapping->ratePlan?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs font-mono">{{ $mapping->channel_room_id }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ $mapping->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $mapping->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('panel.channel.mapping.delete', $mapping->id) }}" onsubmit="return confirm('Hapus mapping ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-medium">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

@endsection
