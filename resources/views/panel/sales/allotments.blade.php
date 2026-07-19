@extends('panel.layout')
@section('title', 'Allotments')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Allotments</h1>
    <p class="text-sm text-gray-500 mt-0.5">Room blocks for travel agents and corporate accounts</p>
</div>

@if (session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

<div class="grid md:grid-cols-3 gap-5">

    {{-- Allotments table --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">TA / Company</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Room Type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Blocked</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Picked</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Left</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Release</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($allotments as $a)
                        @php $remaining = $a->remaining ?? ($a->rooms_blocked - $a->rooms_picked_up); @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-sm text-gray-700">
                                {{ $a->from_date->format('d M') }} – {{ $a->to_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-sm font-medium text-gray-800">
                                {{ $a->travelAgent?->name ?? $a->company?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-600">{{ $a->roomType?->name }}</td>
                            <td class="px-4 py-3.5 text-right text-sm text-gray-700 tabular-nums">{{ $a->rooms_blocked }}</td>
                            <td class="px-4 py-3.5 text-right text-sm text-primary-600 font-medium tabular-nums">{{ $a->rooms_picked_up }}</td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="text-sm font-bold {{ $remaining <= 0 ? 'text-red-500' : ($remaining <= 2 ? 'text-amber-600' : 'text-emerald-700') }} tabular-nums">
                                    {{ $remaining }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-500">
                                {{ $a->release_date?->format('d M') ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('panel.sales.allotments.show', $a->id) }}"
                                       class="text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded-lg transition-colors">
                                        Detail
                                    </a>
                                    <form method="POST" action="{{ route('panel.sales.allotments.release', $a->id) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs font-medium text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 px-2 py-1 rounded-lg transition-colors">
                                            Release
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('panel.sales.allotments.destroy', $a->id) }}" class="inline" onsubmit="return confirm('Hapus allotment ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs font-medium text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-2 py-1 rounded-lg transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-sm text-gray-400">No allotments yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- New allotment form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">New Allotment</h2>
        </div>
        <form method="POST" action="{{ route('panel.sales.allotments.store') }}" class="p-5 space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Travel Agent</label>
                    <select name="travel_agent_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                        <option value="">Pilih Agen</option>
                        @foreach(\App\Models\TravelAgent::where('property_id', app('current_property')->id)->orderBy('name')->get() as $ta)
                        <option value="{{ $ta->id }}">{{ $ta->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Company</label>
                    <select name="company_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                        <option value="">Pilih Perusahaan</option>
                        @foreach(\App\Models\Company::where('property_id', app('current_property')->id)->orderBy('name')->get() as $comp)
                        <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room Type <span class="text-red-500">*</span></label>
                <select name="room_type_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                    <option value="">Pilih Tipe Kamar</option>
                    @foreach(\App\Models\RoomType::where('property_id', app('current_property')->id)->orderBy('name')->get() as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Period <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="from_date" required
                           class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                    <input type="date" name="to_date" required
                           class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Release Date</label>
                <input type="date" name="release_date"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rooms Blocked <span class="text-red-500">*</span></label>
                <input type="number" name="rooms_blocked" required min="1" placeholder="10"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Negotiated Rate (Rp)</label>
                <input type="number" step="1" name="negotiated_rate" placeholder="500000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Create Allotment
            </button>
        </form>
    </div>

</div>

@endsection
