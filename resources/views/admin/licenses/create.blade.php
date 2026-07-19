@extends('admin.layout')

@section('title', 'Create License')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Create License</h1>
    <a href="{{ route('admin.licenses.index') }}" class="text-primary-600">Back</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <p class="text-gray-500 text-center py-12">License creation form coming soon.</p>
</div>
@endsection
