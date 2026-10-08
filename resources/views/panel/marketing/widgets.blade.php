@extends('panel.layout')
@section('title', 'Widget Embed')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Widget Embed</h1>
    <p class="text-sm text-gray-500 mt-0.5">Tempel widget booking & kalender ketersediaan di website lain (blog, WordPress, landing page)</p>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Availability Calendar --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-2">Kalender Ketersediaan</h2>
        <p class="text-xs text-gray-500 mb-4">Menampilkan ketersediaan & harga per malam, auto-refresh. Cocok untuk halaman landing / website travel partner.</p>

        <div class="mb-4">
            <button type="button" onclick="copyEmbed('embed-avail')"
                    class="text-xs font-semibold bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-100">Salin Kode</button>
        </div>
        <textarea id="embed-avail" readonly rows="4" class="w-full font-mono text-xs rounded-xl border-gray-200 bg-gray-50 p-3">{{ $availabilityEmbed }}</textarea>

        <div class="mt-4 rounded-2xl border border-slate-200 overflow-hidden">
            <iframe src="{{ route('widget.availability') }}" style="width:100%;min-height:380px;border:0;" loading="lazy" title="Preview Kalender"></iframe>
        </div>
    </div>

    {{-- Buy Button --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-2">Tombol "Book Now"</h2>
        <p class="text-xs text-gray-500 mb-4">Generate tombol booking yang bisa di-embed. Pilih tipe kamar (opsional) untuk pre-select.</p>

        <form id="bb-form" class="space-y-3 mb-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tipe Kamar (opsional)</label>
                <select id="bb-room" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua kamar</option>
                    @foreach($roomTypes as $rt)
                    <option value="{{ $rt->slug }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Kode Embed (iframe)</label>
                <textarea id="embed-btn" readonly rows="3" class="w-full font-mono text-xs rounded-xl border-gray-200 bg-gray-50 p-3">{{ $baseUrl }}/widget/book-button</textarea>
            </div>
        </form>

        <div class="mb-3">
            <button type="button" onclick="copyEmbed('embed-btn')" class="text-xs font-semibold bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-100">Salin Kode</button>
        </div>

        <div class="rounded-2xl border border-slate-200 overflow-hidden">
            <iframe id="bb-preview" src="{{ route('widget.book-button') }}" style="width:100%;min-height:120px;border:0;" loading="lazy" title="Preview Tombol"></iframe>
        </div>
    </div>
</div>

<script>
function copyEmbed(id) {
    var el = document.getElementById(id);
    el.select();
    document.execCommand('copy');
}

document.getElementById('bb-room').addEventListener('change', function () {
    var base = "{{ $baseUrl }}/widget/book-button";
    var slug = this.value;
    var url = slug ? base + '?room_type=' + encodeURIComponent(slug) : base;
    document.getElementById('embed-btn').value =
        '<iframe src="' + url + '" style="width:100%;min-height:120px;border:0;border-radius:12px;" loading="lazy" title="Book Now"></iframe>';
    document.getElementById('bb-preview').src = url;
});
</script>

@endsection
