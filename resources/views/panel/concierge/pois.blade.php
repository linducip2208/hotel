@extends('panel.layout')
@section('title', 'Points of Interest')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Local Guide — Points of Interest</h1>
    <p class="text-sm text-gray-500 mt-0.5">Curated nearby attractions and recommendations for guests</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- POI list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Place</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Distance</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Rating</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Pick</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($pois as $p)
                        @php
                            $catColors = ['restaurant' => 'orange', 'attraction' => 'blue', 'shopping' => 'pink', 'transport' => 'gray', 'nightlife' => 'violet', 'culture' => 'amber', 'nature' => 'emerald', 'spa' => 'rose'];
                            $cc = $catColors[$p->category] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="text-sm font-semibold text-gray-900">{{ $p->name }}</div>
                                @if ($p->city)
                                <div class="text-xs text-gray-400 mt-0.5">{{ $p->city }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-medium bg-{{ $cc }}-50 text-{{ $cc }}-700 px-2 py-0.5 rounded-full capitalize">{{ $p->category }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right text-sm text-gray-600">
                                {{ $p->distance_meters ? number_format($p->distance_meters / 1000, 1).' km' : '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if ($p->rating)
                                <div class="flex items-center justify-center gap-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-3 h-3 {{ $i <= $p->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    @endfor
                                </div>
                                @else
                                <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if ($p->is_recommended)
                                <svg class="w-4 h-4 text-amber-400 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @else
                                <span class="text-gray-200">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-gray-400">No POIs yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add POI form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Add POI</h2>
        </div>
        <form method="POST" action="{{ route('panel.concierge.pois.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Kuta Beach"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Category <span class="text-red-500">*</span></label>
                <select name="category" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="restaurant">Restaurant</option>
                    <option value="attraction">Attraction</option>
                    <option value="shopping">Shopping</option>
                    <option value="transport">Transport</option>
                    <option value="nightlife">Nightlife</option>
                    <option value="culture">Culture</option>
                    <option value="nature">Nature</option>
                    <option value="spa">Spa</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">City</label>
                <input type="text" name="city" placeholder="Bali"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="Brief description…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Distance (m)</label>
                    <input type="number" name="distance_meters" placeholder="500"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rating (1-5)</label>
                    <input type="number" name="rating" min="1" max="5" placeholder="4"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone</label>
                <input type="text" name="phone" placeholder="+62…"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Add POI
            </button>
        </form>
    </div>

</div>

@endsection
