@extends('admin.layout')

@section('title', 'Edit Admin User')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Edit: {{ $user->name }}</h1>
    <a href="{{ route('admin.admin-users.show', $user) }}" class="text-primary-600">Back</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.admin-users.update', $user) }}" class="space-y-4 max-w-lg">
        @csrf @method('PUT')
        <div><label class="block text-sm font-medium">Name</label><input type="text" name="name" value="{{ $user->name }}" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Role</label><select name="role" class="w-full border rounded p-2">@foreach(['super_admin','sales','support','finance','dev_ops','read_only'] as $r)<option value="{{ $r }}" {{ $user->role === $r ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($r)) }}</option>@endforeach</select></div>
        <div><label class="block text-sm font-medium">Active</label><select name="is_active" class="w-full border rounded p-2"><option value="1" {{ $user->is_active ? 'selected' : '' }}>Yes</option><option value="0" {{ $user->is_active ? '' : 'selected' }}>No</option></select></div>
        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded">Update Admin</button>
    </form>
</div>
@endsection
