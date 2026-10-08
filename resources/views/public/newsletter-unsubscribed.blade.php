@extends('public.layout')
@section('title', 'Berhenti Berlangganan')
@section('content')
<div class="pt-28 lg:pt-36 pb-16">
    <div class="max-w-lg mx-auto px-4 lg:px-8 text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="font-display text-3xl font-bold text-slate-900 mb-3">Anda Telah Berhenti Berlangganan</h1>
        <p class="text-slate-500 leading-relaxed mb-8">Email Anda sudah dikeluarkan dari daftar newsletter kami. Kami sedih melihat Anda pergi — silakan kembali kapan saja.</p>
        <a href="/" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-3 rounded-full transition-colors">Kembali ke Beranda</a>
    </div>
</div>
@endsection
