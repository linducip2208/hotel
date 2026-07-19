@extends('admin.layout')

@section('title', 'License Details')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">License #{{ $event->id }}</h1>
    <a href="{{ route('admin.licenses.index') }}" class="text-primary-600">Back to Licenses</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <dl class="grid grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500">Event</dt><dd class="font-medium">{{ $event->event }}</dd></div>
        <div><dt class="text-gray-500">Created</dt><dd class="font-medium">{{ $event->created_at?->format('Y-m-d H:i:s') }}</dd></div>
    </dl>
</div>
@endsection
