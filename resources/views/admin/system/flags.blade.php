@extends('admin.layout')

@section('title', 'Feature Flags')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Feature Flags</h1>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.system.flags.update') }}" class="space-y-3 max-w-md">
        @csrf @method('PATCH')
        <p class="text-gray-500 text-center py-12">Feature flag management coming soon.</p>
    </form>
</div>
@endsection
