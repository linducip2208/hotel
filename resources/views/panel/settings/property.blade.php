@extends('panel.layout')
@section('title', 'Property Settings')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Property Settings</h1>
    <p class="text-sm text-gray-500 mt-0.5">General property information and defaults</p>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('panel.settings.property.update') }}" class="space-y-6">
        @csrf @method('PATCH')

        {{-- Identity --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Property Identity</h2>
                <p class="text-xs text-gray-400 mt-0.5">Display name and legal information</p>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Property Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $property->name) }}" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Legal Name</label>
                    <input type="text" name="legal_name" value="{{ old('legal_name', $property->legal_name) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Address</h2>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Street Address</label>
                    <input type="text" name="address_line1" value="{{ old('address_line1', $property->address_line1) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">City</label>
                        <input type="text" name="city" value="{{ old('city', $property->city) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Province</label>
                        <input type="text" name="province" value="{{ old('province', $property->province) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code', $property->postal_code) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                    </div>
                </div>
            </div>
        </div>

        {{-- Operations --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Operations</h2>
                <p class="text-xs text-gray-400 mt-0.5">Star rating and check-in/out defaults</p>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Star Rating</label>
                        <select name="star_rating" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                            @for ($s = 1; $s <= 5; $s++)
                            <option value="{{ $s }}" @selected(old('star_rating', $property->star_rating) == $s)>{{ $s }} ★</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Check-in Time</label>
                        <input type="time" name="check_in_time" value="{{ optional($property->check_in_time)->format('H:i') }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Check-out Time</label>
                        <input type="time" name="check_out_time" value="{{ optional($property->check_out_time)->format('H:i') }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save Changes
            </button>
            <a href="{{ route('panel.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Cancel</a>
        </div>

    </form>
</div>

@endsection
