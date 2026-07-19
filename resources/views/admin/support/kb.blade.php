@extends('admin.layout')

@section('title', 'Knowledge Base')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Knowledge Base</h1>
    <a href="{{ route('admin.support.tickets') }}" class="text-primary-600">Back to Support</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <p class="text-gray-500 text-center py-12">Knowledge base articles coming soon.</p>
</div>
@endsection
