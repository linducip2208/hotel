@extends('admin.layout')

@section('title', 'Admin Users')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Admin Users</h1>
    <a href="{{ route('admin.admin-users.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded">Add Admin</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-4">
    @if(($users ?? collect())->isNotEmpty())
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left"><th class="p-2">Name</th><th class="p-2">Email</th><th class="p-2">Role</th><th class="p-2">Actions</th></tr></thead>
            <tbody>
            @foreach($users as $user)
                <tr class="border-b">
                    <td class="p-2">{{ $user->name }}</td>
                    <td class="p-2">{{ $user->email }}</td>
                    <td class="p-2">{{ $user->role }}</td>
                    <td class="p-2"><a href="{{ route('admin.admin-users.show', $user) }}" class="text-primary-600">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">{{ $users->links() }}</div>
        @endif
    @else
        <p class="text-gray-500 text-center py-12">No admin users found.</p>
    @endif
</div>
@endsection
