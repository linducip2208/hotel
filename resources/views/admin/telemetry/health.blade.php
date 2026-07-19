@extends('admin.layout')

@section('title', 'Deployment Health')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Deployment Health</h1>
    <a href="{{ route('admin.telemetry.index') }}" class="text-primary-600">Back</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <p class="text-gray-500 text-center py-12">No health data available.</p>
</div>
@endsection
