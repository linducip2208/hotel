@extends('panel.layout')
@section('title', 'Record New Lost & Found Item')
@section('content')

<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <a href="{{ route('panel.hk.lost-found.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Lost & Found
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Record Found Item</h1>
        <p class="text-sm text-gray-500 mt-1">Log a new lost item found on the property</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
        <form method="POST" action="{{ route('panel.hk.lost-found.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Item Description <span class="text-red-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description') }}" required
                           placeholder="e.g. Black leather wallet, Gold ring..."
                           class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 bg-white text-gray-900 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Location Found</label>
                    <input type="text" name="found_location" value="{{ old('found_location') }}"
                           placeholder="e.g. Lobby, Room 101, Restaurant..."
                           class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 bg-white text-gray-900 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                    @error('found_location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date Found <span class="text-red-500">*</span></label>
                        <input type="date" name="found_date" value="{{ old('found_date', now()->toDateString()) }}" required
                               class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 bg-white text-gray-900 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        @error('found_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Room (if applicable)</label>
                        <select name="room_id" class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 bg-white text-gray-700 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Not room-specific</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>Room {{ $room->number }} — {{ $room->roomType?->name }}</option>
                            @endforeach
                        </select>
                        @error('room_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Found By</label>
                    <input type="text" name="found_by" value="{{ old('found_by') }}"
                           placeholder="Name of person who found the item"
                           class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 bg-white text-gray-900 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                    @error('found_by') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Photo</label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 bg-white text-gray-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-400 mt-1">Max 5MB. JPEG, PNG, or WebP.</p>
                    @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea name="notes" rows="3" placeholder="Any additional details..."
                              class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 bg-white text-gray-900 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-none">{{ old('notes') }}</textarea>
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 mt-8 pt-6 border-t border-gray-100">
                <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-6 py-2.5 rounded-xl transition shadow-sm">
                    Save Item
                </button>
                <a href="{{ route('panel.hk.lost-found.index') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium px-6 py-2.5 rounded-xl transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
