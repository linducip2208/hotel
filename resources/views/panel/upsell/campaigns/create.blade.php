@extends('panel.layout')
@section('title', 'Create Upsell Campaign')
@section('content')
<div class="mb-6">
    <a href="{{ route('panel.upsell.campaigns.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Campaigns</a>
    <h2 class="text-xl font-bold text-slate-800 mt-1">Create Upsell Campaign</h2>
</div>
<form method="POST" action="{{ route('panel.upsell.campaigns.store') }}" class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm max-w-2xl">
    @csrf
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Campaign</label>
            <input name="name" required class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Offers</label>
            <div class="space-y-2 max-h-48 overflow-y-auto border border-slate-200 rounded-xl p-3">
                @foreach($offers as $offer)
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="offer_ids[]" value="{{ $offer->id }}" class="rounded border-slate-300 text-indigo-600">
                    {{ $offer->name }} — Rp {{ number_format($offer->price, 0, ',', '.') }}
                </label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Hari Sebelum Check-in</label>
            <select name="days_before_arrival" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5">
                @for($i=1; $i<=14; $i++)
                <option value="{{ $i }}" {{ $i==3 ? 'selected' : '' }}>{{ $i }} hari</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Channel</label>
            <select name="channel" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5">
                <option value="whatsapp">WhatsApp</option>
                <option value="email">Email</option>
                <option value="both">Both</option>
            </select>
        </div>
        <button class="bg-rose-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-rose-700">Buat Campaign</button>
    </div>
</form>
@endsection
