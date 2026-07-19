@extends('admin.layout')

@section('title', 'Tenants')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Tenants</h1>
    <a href="{{ route('admin.tenants.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded">Create Tenant</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-4">
    @if(($tenants ?? collect())->isNotEmpty())
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left"><th class="p-2">Company</th><th class="p-2">Slug</th><th class="p-2">Status</th><th class="p-2">Plan</th><th class="p-2">Actions</th></tr></thead>
            <tbody>
            @foreach($tenants as $tenant)
                <tr class="border-b">
                    <td class="p-2">{{ $tenant->company_name }}</td>
                    <td class="p-2">{{ $tenant->slug }}</td>
                    <td class="p-2"><span class="px-2 py-0.5 rounded text-xs {{ $tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $tenant->status }}</span></td>
                    <td class="p-2">{{ $tenant->plan?->name ?? '—' }}</td>
                    <td class="p-2"><a href="{{ route('admin.tenants.show', $tenant) }}" class="text-primary-600">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($tenants instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">{{ $tenants->links() }}</div>
        @endif
    @else
        <p class="text-gray-500 text-center py-12">No tenants found.</p>
    @endif
</div>
@endsection
