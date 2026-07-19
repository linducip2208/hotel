@extends('admin.layout')

@section('title', 'Billing Subscriptions')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Subscriptions</h1>
    <a href="{{ route('admin.billing.index') }}" class="text-primary-600">Back to Billing</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-6">
    <p class="text-gray-500 text-center py-12">Subscription management coming soon.</p>
</div>
@endsection
