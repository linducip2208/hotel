@extends('admin.layout')

@section('title', 'Edit Tenant')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Edit: {{ $tenant->company_name }}</h1>
    <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-primary-600">Back</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" class="space-y-4 max-w-lg">
        @csrf @method('PUT')
        <div><label class="block text-sm font-medium">Company Name</label><input type="text" name="company_name" value="{{ $tenant->company_name }}" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Slug</label><input type="text" name="slug" value="{{ $tenant->slug }}" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Owner Name</label><input type="text" name="owner_name" value="{{ $tenant->owner_name }}" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Owner Email</label><input type="email" name="owner_email" value="{{ $tenant->owner_email }}" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Status</label><select name="status" class="w-full border rounded p-2">@foreach(['trial','active','suspended','churned'] as $s)<option value="{{ $s }}" {{ $tenant->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div><label class="block text-sm font-medium">Plan</label><select name="plan_id" class="w-full border rounded p-2"><option value="">—</option>@foreach($plans ?? [] as $p)<option value="{{ $p->id }}" {{ $tenant->plan_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></div>
        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded">Update Tenant</button>
    </form>
</div>
@endsection
