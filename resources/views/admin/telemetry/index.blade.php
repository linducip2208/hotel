@extends('admin.layout')

@section('title', 'Telemetry Dashboard')

@section('content')
<h1 class="text-2xl font-bold mb-6">Telemetry Dashboard</h1>
<div class="grid md:grid-cols-3 gap-4">
    <a href="{{ route('admin.telemetry.health') }}" class="bg-white rounded shadow border border-gray-100 p-6 hover:border-primary-300">
        <h2 class="font-bold text-lg">Health</h2><p class="text-gray-500 text-sm mt-1">Deployment health overview.</p>
    </a>
    <a href="{{ route('admin.telemetry.errors') }}" class="bg-white rounded shadow border border-gray-100 p-6 hover:border-primary-300">
        <h2 class="font-bold text-lg">Errors</h2><p class="text-gray-500 text-sm mt-1">Error reports from deployments.</p>
    </a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6 mt-4">
    <p class="text-gray-500 text-center py-6">No telemetry data yet.</p>
</div>
@endsection
