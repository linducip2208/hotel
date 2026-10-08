@extends('admin.layout')
@section('title', 'Feature Flags')

@section('content')
@if (session('status'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded text-sm">{{ session('status') }}</div>
@endif
@if ($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded text-sm">{{ $errors->first() }}</div>
@endif

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Feature Flags</h1>
    <p class="text-sm text-gray-500 mt-1">Override konfigurasi fitur — tersimpan persisten dan langsung aktif.</p>
</div>

<form method="POST" action="{{ route('admin.system.flags.update') }}" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 max-w-2xl">
    @csrf
    @method('PATCH')
    @foreach ($features as $key => $default)
    @php $current = $overrides[$key] ?? $default; @endphp
    <label class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 cursor-pointer">
        <div>
            <p class="text-sm font-medium text-gray-900 font-mono">{{ $key }}</p>
            <p class="text-xs text-gray-400">default: {{ $default ? 'on' : 'off' }}{{ isset($overrides[$key]) ? ' · overridden' : '' }}</p>
        </div>
        <input type="hidden" name="flags[{{ $key }}]" value="0">
        <input type="checkbox" name="flags[{{ $key }}]" value="1" {{ $current ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-primary-600">
    </label>
    @endforeach
    <div class="px-5 py-4">
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg">Simpan Flags</button>
    </div>
</form>
@endsection
