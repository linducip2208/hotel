@extends('panel.layout')
@section('title', 'Leave Balance')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Leave Balance — {{ $employee->full_name }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">Year {{ $year }}</p>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Annual Leave</h2>
        <div class="space-y-3">
            <div class="flex justify-between text-sm"><span class="text-gray-500">Total</span><span class="font-bold text-gray-900">{{ $balance->total_annual }} days</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Used</span><span class="font-bold text-red-600">{{ $balance->used_annual }} days</span></div>
            <div class="flex justify-between text-sm border-t pt-2"><span class="text-gray-500">Remaining</span><span class="font-bold text-emerald-600">{{ $balance->total_annual - $balance->used_annual }} days</span></div>
        </div>
        <div class="mt-4 h-3 bg-gray-100 rounded-full overflow-hidden">
            @php $pct = $balance->total_annual > 0 ? ($balance->used_annual / $balance->total_annual) * 100 : 0; @endphp
            <div class="h-full rounded-full {{ $pct > 80 ? 'bg-red-500' : 'bg-emerald-500' }}" style="width:{{ $pct }}%"></div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Sick Leave</h2>
        <div class="space-y-3">
            <div class="flex justify-between text-sm"><span class="text-gray-500">Total</span><span class="font-bold text-gray-900">{{ $balance->total_sick }} days</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Used</span><span class="font-bold text-red-600">{{ $balance->used_sick }} days</span></div>
            <div class="flex justify-between text-sm border-t pt-2"><span class="text-gray-500">Remaining</span><span class="font-bold text-emerald-600">{{ $balance->total_sick - $balance->used_sick }} days</span></div>
        </div>
    </div>
</div>

<a href="{{ route('panel.hr.leave.index') }}" class="inline-flex items-center gap-1 text-sm text-primary-600 hover:underline mt-4">Back to Leave Requests</a>

@endsection
