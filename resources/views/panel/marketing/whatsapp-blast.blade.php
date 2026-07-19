@extends('panel.layout')
@section('title', 'WhatsApp Blast Marketing')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">WhatsApp Blast Marketing</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kirim pesan massal ke tamu via ChatGo WhatsApp Gateway</p>
</div>

@if(!$provider)
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center">
    <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <h3 class="text-lg font-semibold text-amber-800 mb-1">Provider ChatGo Belum Dikonfigurasi</h3>
    <p class="text-sm text-amber-600 mb-4">Tambahkan provider WhatsApp dengan format ChatGo di halaman integrasi untuk mulai mengirim blast.</p>
    <a href="{{ route('panel.settings.integrations') }}" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066"/></svg>
        Buka Pengaturan Integrasi
    </a>
</div>
@else
{{-- Status Card --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-6">
    <div class="flex items-center gap-4 flex-wrap">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ $provider->name }}</p>
                <p class="text-xs text-gray-500">{{ $provider->base_url ?? 'https://chatgo.whitelabel.co.id' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-xs text-gray-600">Terkonfigurasi</span>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Blast Form --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Kirim Blast WhatsApp</h2>
        </div>
        <form method="POST" action="{{ route('panel.marketing.whatsapp-blast.send') }}" class="p-5 space-y-4">
            @csrf

            {{-- Segment --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Segmentasi Tamu</label>
                <select name="segment" id="segment"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">Semua Tamu (dengan nomor HP)</option>
                    @foreach($segments as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Message Template --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Template Pesan</label>
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach($templates as $idx => $tmpl)
                    <button type="button" onclick="document.getElementById('message').value = @js($tmpl)"
                            class="text-xs bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 text-gray-600 px-3 py-1.5 rounded-lg transition-colors">
                        Template {{ $idx + 1 }}
                    </button>
                    @endforeach
                </div>
                <textarea id="message" name="message" rows="5" required
                          placeholder="Tulis pesan WhatsApp... Gunakan {name}, {stays}, {ltv} untuk personalisasi."
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all"></textarea>
                <p class="text-[11px] text-gray-400 mt-1">Variabel: <code class="bg-gray-100 px-1 rounded">{name}</code> <code class="bg-gray-100 px-1 rounded">{stays}</code> <code class="bg-gray-100 px-1 rounded">{ltv}</code> <code class="bg-gray-100 px-1 rounded">{phone}</code> <code class="bg-gray-100 px-1 rounded">{email}</code></p>
            </div>

            {{-- Delay --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jeda Antar Pesan (detik)</label>
                <input type="number" name="delay" value="5" min="1" max="60"
                       class="w-32 rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>

            {{-- Preview Recipients --}}
            <div>
                <button type="button" id="btnPreview"
                        class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Pratinjau Penerima
                </button>
                <div id="previewResult" class="mt-3 hidden">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Penerima: <span id="recipientCount" class="text-indigo-600">0</span> tamu</p>
                        <div id="recipientList" class="max-h-48 overflow-y-auto text-xs text-gray-600 space-y-0.5"></div>
                    </div>
                </div>
            </div>

            {{-- Guest IDs hidden input --}}
            <input type="hidden" name="guest_ids" id="guestIdsInput" value="">

            {{-- Submit --}}
            <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Kirim Blast Sekarang
            </button>
        </form>
    </div>

    {{-- Test Send --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Uji Kirim</h2>
        </div>
        <form method="POST" action="{{ route('panel.marketing.whatsapp-blast.test') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nomor HP Tujuan</label>
                <input type="text" name="phone" required placeholder="08123456789"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Pesan</label>
                <textarea name="message" rows="3" required placeholder="Pesan uji coba..."
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all"></textarea>
            </div>
            <button type="submit"
                    class="w-full bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Kirim Uji Coba
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('btnPreview').addEventListener('click', function() {
    const segment = document.getElementById('segment').value;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('{{ route('panel.marketing.whatsapp-blast.preview') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify({ segment: segment })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('recipientCount').textContent = data.count;
        const list = document.getElementById('recipientList');
        list.innerHTML = Object.entries(data.guests).slice(0, 100).map(([id, name]) =>
            '<div class="flex items-center gap-2"><input type="checkbox" checked value="' + id + '" class="guest-check rounded"> <span>' + name + ' (ID: ' + id + ')</span></div>'
        ).join('');
        if (Object.keys(data.guests).length > 100) {
            list.innerHTML += '<p class="text-gray-400 mt-1">+ ' + (Object.keys(data.guests).length - 100) + ' tamu lainnya</p>';
        }
        document.getElementById('previewResult').classList.remove('hidden');
        updateGuestIds();
    });
});

document.getElementById('previewResult').addEventListener('change', function(e) {
    if (e.target.classList.contains('guest-check')) {
        updateGuestIds();
    }
});

function updateGuestIds() {
    const checked = document.querySelectorAll('.guest-check:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    document.getElementById('guestIdsInput').value = JSON.stringify(ids);
}
</script>
@endpush

@endif

@endsection
