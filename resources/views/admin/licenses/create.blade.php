@extends('admin.layout')
@section('title', 'Create License')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Issue License</h1>
    <p class="text-sm text-gray-500 mt-1">Catat penerbitan lisensi untuk domain customer</p>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-xl">
    <form method="POST" action="{{ route('admin.licenses.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">License Key <span class="text-red-500">*</span></label>
            <input type="text" name="license_key" required maxlength="255" value="{{ old('license_key') }}" placeholder="LIC-XXXX-XXXX-XXXX"
                   class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm focus:border-primary-400 outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Domain <span class="text-red-500">*</span></label>
            <input type="text" name="domain" required maxlength="255" value="{{ old('domain') }}" placeholder="hotel-customer.com"
                   class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm focus:border-primary-400 outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan</label>
            <textarea name="notes" rows="2" maxlength="1000" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm resize-none">{{ old('notes') }}</textarea>
        </div>
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg">Issue License</button>
    </form>
</div>
@endsection
