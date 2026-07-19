@extends('admin.layout')

@section('title', 'Email Templates')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Email Templates</h1>
    <a href="{{ route('admin.system.flags') }}" class="text-primary-600">Back to System</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <p class="text-gray-500 text-center py-12">Email template management coming soon.</p>
</div>
@endsection
