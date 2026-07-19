@extends('admin.layout')

@section('title', 'Add Admin User')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Add Admin User</h1>
    <a href="{{ route('admin.admin-users.index') }}" class="text-primary-600">Back</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.admin-users.store') }}" class="space-y-4 max-w-lg">
        @csrf
        <div><label class="block text-sm font-medium">Name</label><input type="text" name="name" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Email</label><input type="email" name="email" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Password</label><input type="password" name="password" required minlength="10" class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Role</label><select name="role" required class="w-full border rounded p-2">@foreach(['super_admin','sales','support','finance','dev_ops','read_only'] as $r)<option value="{{ $r }}">{{ str_replace('_', ' ', ucfirst($r)) }}</option>@endforeach</select></div>
        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded">Create Admin</button>
    </form>
</div>
@endsection
