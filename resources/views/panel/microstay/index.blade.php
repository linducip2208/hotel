@extends('panel.layout')
@section('title', 'Micro-stay / Day-use')
@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-800">Micro-stay / Day-use</h2>
    <p class="text-sm text-slate-500">Booking kamar per jam (3/6/12 jam)</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    @foreach([3,6,12] as $hr)
    @php $count = $rates->where('hours', $hr)->count(); @endphp
    <a href="{{ route('panel.microstay.rates') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-xs font-bold text-indigo-500 px-2 py-0.5 bg-indigo-50 rounded-full">{{ $count }} rates</span>
        </div>
        <div class="text-2xl font-bold text-slate-800">{{ $hr }} Jam</div>
        <p class="text-sm text-slate-500 mt-1">Durasi menginap</p>
    </a>
    @endforeach
</div>

<form method="POST" action="{{ route('panel.microstay.book') }}" class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
    @csrf
    <h3 class="font-semibold text-slate-800 mb-4">Booking Micro-stay Baru</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Kamar</label>
            <select name="room_type_id" required class="w-full border-slate-300 rounded-xl text-sm">
                @foreach(\App\Models\RoomType::where('property_id', $property->id)->where('is_active',true)->get() as $rt)
                <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Durasi</label>
            <select name="hours" required class="w-full border-slate-300 rounded-xl text-sm">
                <option value="3">3 Jam</option>
                <option value="6">6 Jam</option>
                <option value="12">12 Jam</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Check-in</label>
            <input type="datetime-local" name="check_in" required class="w-full border-slate-300 rounded-xl text-sm" value="{{ now()->format('Y-m-d\TH:i') }}">
        </div>
    </div>
    <button class="mt-4 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700">Booking Sekarang</button>
</form>
@endsection
