@extends('panel.layout')
@section('title', 'Testimonial')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Testimonial</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kurasi testimoni tamu yang tampil di halaman depan website</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Form --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Tambah Testimoni</h2>
            <form method="POST" action="{{ route('panel.marketing.testimonials.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Tamu</label>
                    <input type="text" name="guest_name" required class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Label (opsional)</label>
                    <input type="text" name="guest_title" placeholder="cth: Business Traveler" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Asal Kota (opsional)</label>
                    <input type="text" name="origin_city" placeholder="cth: Jakarta" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Rating (1-5)</label>
                    <select name="rating" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                        @for($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }} bintang</option>@endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Kutipan</label>
                    <textarea name="quote" required rows="4" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Simpan</button>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="lg:col-span-2 space-y-4">
        @forelse ($testimonials as $t)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <form method="POST" action="{{ route('panel.marketing.testimonials.update', $t->id) }}">
                @csrf @method('PUT')
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-white font-semibold text-sm">{{ strtoupper(substr($t->guest_name, 0, 1)) }}</div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $t->guest_name }}</p>
                            <p class="text-xs text-gray-500">{{ $t->guest_title }}{{ $t->origin_city ? ' · ' . $t->origin_city : '' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-4 h-4 {{ $i < $t->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3 mb-3">
                    <input type="text" name="guest_name" value="{{ $t->guest_name }}" class="rounded-xl border-gray-200 text-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" name="guest_title" value="{{ $t->guest_title }}" placeholder="Label" class="rounded-xl border-gray-200 text-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" name="origin_city" value="{{ $t->origin_city }}" placeholder="Asal kota" class="rounded-xl border-gray-200 text-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="number" name="sort_order" value="{{ $t->sort_order }}" placeholder="Urutan" class="rounded-xl border-gray-200 text-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <textarea name="quote" rows="2" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2 mb-3 focus:ring-indigo-500 focus:border-indigo-500">{{ $t->quote }}</textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                        Tampilkan di website
                    </label>
                    <div class="flex items-center gap-1">
                        <button class="px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100">Simpan</button>
                        <button form="del-testimonial-{{ $t->id }}" class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg">Hapus</button>
                    </div>
                </div>
            </form>
            <form id="del-testimonial-{{ $t->id }}" method="POST" action="{{ route('panel.marketing.testimonials.destroy', $t->id) }}" onsubmit="return confirm('Hapus testimoni ini?')">@csrf @method('DELETE')</form>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-10 text-center text-gray-400">Belum ada testimoni.</div>
        @endforelse
    </div>
</div>

@endsection
