@extends('admin.layout')

@section('title', 'Billing Dashboard')

@section('content')
<h1 class="text-2xl font-bold mb-6">Billing Dashboard</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded shadow border border-gray-100 p-5">
        <p class="text-xs text-gray-500 uppercase font-semibold">Active</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $activeCount }}</p>
    </div>
    <div class="bg-white rounded shadow border border-gray-100 p-5">
        <p class="text-xs text-gray-500 uppercase font-semibold">Trialing</p>
        <p class="text-2xl font-bold text-primary-600 mt-1">{{ $trialingCount }}</p>
    </div>
    <div class="bg-white rounded shadow border border-gray-100 p-5">
        <p class="text-xs text-gray-500 uppercase font-semibold">Past Due</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ $pastDueCount }}</p>
    </div>
    <div class="bg-white rounded shadow border border-gray-100 p-5">
        <p class="text-xs text-gray-500 uppercase font-semibold">Outstanding</p>
        <p class="text-lg font-bold text-gray-900 mt-1">Rp {{ number_format($outstanding, 0, ',', '.') }}</p>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-4">
    <a href="{{ route('admin.billing.subscriptions') }}" class="bg-white rounded shadow border border-gray-100 p-6 hover:border-primary-300">
        <h2 class="font-bold text-lg">Subscriptions</h2><p class="text-gray-500 text-sm mt-1">Manage active tenant subscriptions.</p>
    </a>
    <a href="{{ route('admin.billing.invoices') }}" class="bg-white rounded shadow border border-gray-100 p-6 hover:border-primary-300">
        <h2 class="font-bold text-lg">Invoices</h2><p class="text-gray-500 text-sm mt-1">View all billing invoices.</p>
    </a>
    <a href="{{ route('admin.billing.coupons') }}" class="bg-white rounded shadow border border-gray-100 p-6 hover:border-primary-300">
        <h2 class="font-bold text-lg">Coupons</h2><p class="text-gray-500 text-sm mt-1">Manage discount coupons.</p>
    </a>
    <a href="{{ route('admin.billing.failed') }}" class="bg-white rounded shadow border border-gray-100 p-6 hover:border-primary-300">
        <h2 class="font-bold text-lg">Failed Payments</h2><p class="text-gray-500 text-sm mt-1">Review failed payment attempts.</p>
    </a>
</div>
@endsection
