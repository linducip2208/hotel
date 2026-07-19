@extends('admin.layout')

@section('title', 'Telemetry Errors')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Error Reports</h1>
    <a href="{{ route('admin.telemetry.index') }}" class="text-primary-600">Back</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <p class="text-gray-500 text-center py-12">No errors reported.</p>
</div>
@endsection
