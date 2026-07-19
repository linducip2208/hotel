@extends('panel.layout')
@section('title', 'Micro-stay Rates')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Micro-stay Rates</h2>
        <p class="text-sm text-slate-500">Atur tarif per jam untuk setiap tipe kamar</p>
    </div>
    <a href="{{ route('panel.microstay.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali</a>
</div>

<form method="POST" action="{{ route('panel.microstay.rates.store') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm mb-6">
    @csrf
    <h3 class="font-semibold text-slate-800 mb-3">Tambah Rate Baru</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <select name="room_type_id" required class="border-slate-300 rounded-xl text-sm px-3 py-2">
            @foreach($roomTypes as $rt)
            <option value="{{ $rt->id }}">{{ $rt->name }}</option>
            @endforeach
        </select>
        <select name="hours" required class="border-slate-300 rounded-xl text-sm px-3 py-2">
            <option value="3">3 Jam</option>
            <option value="6">6 Jam</option>
            <option value="12">12 Jam</option>
        </select>
        <input name="price" type="number" placeholder="Harga (Rp)" required class="border-slate-300 rounded-xl text-sm px-3 py-2">
        <div class="flex gap-2">
            <input name="earliest_checkin" type="time" value="08:00" required class="border-slate-300 rounded-xl text-sm px-3 py-2 flex-1">
            <input name="latest_checkin" type="time" value="22:00" required class="border-slate-300 rounded-xl text-sm px-3 py-2 flex-1">
        </div>
    </div>
    <button class="mt-3 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-700">Simpan</button>
</form>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Tipe Kamar</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Durasi</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Harga</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Check-in Window</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($rates as $rate)
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800">{{ $rate->roomType->name ?? '—' }}</td>
                <td class="px-4 py-3">{{ $rate->hours }} Jam</td>
                <td class="px-4 py-3 font-bold">Rp {{ number_format($rate->price, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $rate->earliest_checkin }} — {{ $rate->latest_checkin }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('panel.microstay.rates.update', $rate->id) }}" class="inline">
                        @csrf @method('PUT')
                        <button name="is_active" value="{{ $rate->is_active ? 0 : 1 }}" class="text-xs font-bold px-2 py-0.5 rounded-full {{ $rate->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $rate->is_active ? 'Active' : 'Inactive' }}</button>
                    </form>
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('panel.microstay.rates.destroy', $rate->id) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button class="text-rose-500 text-xs hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
