@extends('admin.layout')

@section('title', 'Tenant Details')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ $tenant->company_name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.tenants.edit', $tenant) }}" class="bg-primary-600 text-white px-4 py-2 rounded text-sm">Edit</a>
        <a href="{{ route('admin.tenants.index') }}" class="text-primary-600">Back</a>
    </div>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <dl class="grid grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500">Slug</dt><dd class="font-medium">{{ $tenant->slug }}</dd></div>
        <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ $tenant->status }}</dd></div>
        <div><dt class="text-gray-500">Owner</dt><dd class="font-medium">{{ $tenant->owner_name }}</dd></div>
        <div><dt class="text-gray-500">Email</dt><dd class="font-medium">{{ $tenant->owner_email }}</dd></div>
        <div><dt class="text-gray-500">Plan</dt><dd class="font-medium">{{ $tenant->plan?->name ?? '—' }}</dd></div>
        <div><dt class="text-gray-500">Trial Ends</dt><dd class="font-medium">{{ $tenant->trial_ends_at?->format('Y-m-d') ?? '—' }}</dd></div>
    </dl>
</div>
@endsection
