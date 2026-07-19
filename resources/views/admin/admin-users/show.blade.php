@extends('admin.layout')

@section('title', 'Admin User Details')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('admin.admin-users.edit', $user) }}" class="bg-primary-600 text-white px-4 py-2 rounded text-sm">Edit</a>
        <a href="{{ route('admin.admin-users.index') }}" class="text-primary-600">Back</a>
    </div>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <dl class="grid grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500">Name</dt><dd class="font-medium">{{ $user->name }}</dd></div>
        <div><dt class="text-gray-500">Email</dt><dd class="font-medium">{{ $user->email }}</dd></div>
        <div><dt class="text-gray-500">Role</dt><dd class="font-medium">{{ $user->role }}</dd></div>
        <div><dt class="text-gray-500">Active</dt><dd class="font-medium">{{ $user->is_active ? 'Yes' : 'No' }}</dd></div>
    </dl>
</div>
@endsection
