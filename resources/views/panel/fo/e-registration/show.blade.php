@extends('panel.layout')
@section('title', 'E-Registration Card')
@section('content')

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('panel.fo.e-registration.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Registration Cards
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Registration Card</h1>
                <p class="text-sm text-gray-500 mt-0.5">Reservation: <span class="font-mono font-medium text-gray-700">{{ $card->reservation?->ref }}</span></p>
            </div>
            @if (! $card->is_verified)
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('panel.fo.e-registration.verify', $card->id) }}">
                    @csrf
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        Verify
                    </button>
                </form>
                <button onclick="document.getElementById('reject-form').classList.toggle('hidden')"
                        class="bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                    Reject
                </button>
            </div>
            @else
            <span class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Verified by {{ $card->verifiedByStaff?->name }}
            </span>
            @endif
        </div>

        {{-- Reject form (hidden by default) --}}
        <div id="reject-form" class="hidden px-6 py-4 border-b border-gray-100 bg-red-50">
            <form method="POST" action="{{ route('panel.fo.e-registration.reject', $card->id) }}">
                @csrf
                <label class="block text-sm font-medium text-red-700 mb-1.5">Rejection reason</label>
                <div class="flex gap-2">
                    <input type="text" name="reason" required placeholder="Enter reason for rejection..." class="flex-1 text-sm border border-red-200 rounded-lg px-3 py-2 bg-white text-red-900 focus:ring-1 focus:ring-red-500 focus:border-red-500">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">Confirm Reject</button>
                </div>
            </form>
        </div>

        {{-- Guest Info --}}
        <div class="px-6 py-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Submitted Data</h3>

            @if ($card->submitted_data)
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Full Name</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['full_name'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">ID Type</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['id_type'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">ID Number</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['id_number'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Nationality</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['nationality'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Date of Birth</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['date_of_birth'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Phone</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['phone'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Email</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['email'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Address</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['address'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Vehicle Plate</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['vehicle_plate'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Purpose of Stay</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['purpose_of_stay'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Next Destination</dt>
                    <dd class="text-gray-900 font-medium mt-0.5">{{ $card->submitted_data['next_destination'] ?? '—' }}</dd>
                </div>
            </dl>
            @else
                <p class="text-sm text-gray-400">No submitted data available.</p>
            @endif
        </div>

        {{-- Signature --}}
        @if ($card->signature_image_path)
        <div class="px-6 py-5 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Signature</h3>
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 flex items-center justify-center">
                <img src="{{ Storage::url($card->signature_image_path) }}" class="max-w-full max-h-40 object-contain">
            </div>
        </div>
        @endif

        {{-- Metadata --}}
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-400">
                <span>Signed: {{ $card->signed_at?->format('d M Y H:i') ?: '—' }}</span>
                <span>IP: {{ $card->ip_address ?: '—' }}</span>
            </div>
        </div>
    </div>
</div>

@endsection
