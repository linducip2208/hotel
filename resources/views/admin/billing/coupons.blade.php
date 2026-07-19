@extends('admin.layout')

@section('title', 'Billing Coupons')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Coupons</h1>
    <a href="{{ route('admin.billing.index') }}" class="text-primary-600">Back to Billing</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <form method="POST" action="{{ route('admin.billing.coupons.store') }}" class="space-y-3 max-w-md mb-6">
        @csrf
        <div><label class="block text-sm font-medium">Code</label><input type="text" name="code" required class="w-full border rounded p-2"></div>
        <div><label class="block text-sm font-medium">Discount (%)</label><input type="number" name="discount_pct" step="0.01" class="w-full border rounded p-2"></div>
        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded">Create Coupon</button>
    </form>
    <p class="text-gray-500 text-center py-6">No coupons defined yet.</p>
</div>
@endsection
