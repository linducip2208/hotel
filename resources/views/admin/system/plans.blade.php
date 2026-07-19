@extends('admin.layout')

@section('title', 'Subscription Plans')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Subscription Plans</h1>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.system.plans.store') }}" class="space-y-3 max-w-md mb-6">
        @csrf
        <div><label class="block text-sm font-medium">Plan Name</label><input type="text" name="name" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Slug</label><input type="text" name="slug" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Monthly Price (IDR)</label><input type="number" name="monthly_price_idr" step="1" class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Per Room Price (IDR)</label><input type="number" name="per_room_price_idr" step="1" class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Max Rooms</label><input type="number" name="max_rooms" class="w-full border rounded p-2"></div>
        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded">Create Plan</button>
    </form>
    @if(($plans ?? collect())->isNotEmpty())
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left"><th class="p-2">Name</th><th class="p-2">Slug</th><th class="p-2">Monthly</th><th class="p-2">Per Room</th><th class="p-2">Max Rooms</th></tr></thead>
            <tbody>
            @foreach($plans as $plan)
                <tr class="border-b">
                    <td class="p-2">{{ $plan->name }}</td>
                    <td class="p-2">{{ $plan->slug }}</td>
                    <td class="p-2">{{ number_format($plan->monthly_price_idr ?? 0) }}</td>
                    <td class="p-2">{{ number_format($plan->per_room_price_idr ?? 0) }}</td>
                    <td class="p-2">{{ $plan->max_rooms ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500 text-center py-6">No plans defined yet.</p>
    @endif
</div>
@endsection
