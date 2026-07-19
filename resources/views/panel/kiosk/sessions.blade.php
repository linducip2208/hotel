@extends('panel.layout')
@section('title', 'Kiosk Sessions')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Kiosk Check-in Sessions</h2>
        <p class="text-sm text-slate-500">Self check-in via lobby kiosk</p>
    </div>
    <a href="/kiosk" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        Buka Kiosk
    </a>
</div>

@if(empty($sessions))
<div class="bg-white rounded-2xl p-12 border border-slate-200 shadow-sm text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
    </div>
    <p class="text-slate-500">Tidak ada sesi kiosk aktif</p>
</div>
@else
<div class="grid gap-4">
    @foreach($sessions as $session)
    <a href="{{ route('panel.kiosk.sessions.show', $session['id']) }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-{{ $session['status'] === 'verified' ? 'emerald' : 'indigo' }}-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-{{ $session['status'] === 'verified' ? 'emerald' : 'indigo' }}-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="font-semibold text-slate-800">{{ $session['reservation']['primary_guest']['first_name'] ?? 'Guest' }} {{ $session['reservation']['primary_guest']['last_name'] ?? '' }}</p>
                <p class="text-xs text-slate-500">Ref: {{ $session['reservation']['ref'] ?? '—' }}</p>
            </div>
        </div>
        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-{{ $session['status'] === 'verified' ? 'emerald' : 'indigo' }}-50 text-{{ $session['status'] === 'verified' ? 'emerald' : 'indigo' }}-700">{{ ucfirst($session['status']) }}</span>
    </a>
    @endforeach
</div>
@endif
@endsection
