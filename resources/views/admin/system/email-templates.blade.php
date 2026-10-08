@extends('admin.layout')
@section('title', 'Email Templates')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Email Templates</h1>
    <p class="text-sm text-gray-500 mt-1">Inventaris template mail Blade terpasang (resources/views/mail)</p>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden max-w-3xl">
    @forelse ($templates as $t)
    <div class="px-5 py-3 border-b border-gray-100 font-mono text-sm text-gray-700">{{ $t }}</div>
    @empty
    <div class="py-12 text-center text-sm text-gray-400">Belum ada template mail.</div>
    @endforelse
</div>
@endsection
