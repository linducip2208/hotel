@extends('admin.layout')

@section('title', 'Failed Payments')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Failed Payments</h1>
    <a href="{{ route('admin.billing.index') }}" class="text-primary-600">Back to Billing</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <p class="text-gray-500 text-center py-12">No failed payments to display.</p>
</div>
@endsection
