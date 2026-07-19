@extends('admin.layout')

@section('title', 'Create Tenant')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Create Tenant</h1>
    <a href="{{ route('admin.tenants.index') }}" class="text-primary-600">Back</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.tenants.store') }}" class="space-y-4 max-w-lg">
        @csrf
        <div><label class="block text-sm font-medium">Company Name</label><input type="text" name="company_name" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Slug</label><input type="text" name="slug" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Owner Name</label><input type="text" name="owner_name" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Owner Email</label><input type="email" name="owner_email" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Plan</label><select name="plan_id" class="w-full border rounded p-2"><option value="">—</option>@foreach($plans ?? [] as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded">Create Tenant</button>
    </form>
</div>
@endsection
