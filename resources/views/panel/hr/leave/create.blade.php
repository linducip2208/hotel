@extends('panel.layout')
@section('title', 'Request Leave')
@section('content')

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Request Leave</h1>

    <form method="POST" action="{{ route('panel.hr.leave.store') }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Employee <span class="text-red-500">*</span></label>
            <select name="employee_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <option value="">Select employee</option>
                @foreach ($employees as $e)
                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Leave Type <span class="text-red-500">*</span></label>
            <select name="type" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <option value="annual">Annual Leave</option>
                <option value="sick">Sick Leave</option>
                <option value="maternity">Maternity</option>
                <option value="paternity">Paternity</option>
                <option value="unpaid">Unpaid Leave</option>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Start Date <span class="text-red-500">*</span></label>
                <input type="date" name="start_date" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">End Date <span class="text-red-500">*</span></label>
                <input type="date" name="end_date" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Reason</label>
            <textarea name="reason" rows="3" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm"></textarea>
        </div>
        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl shadow-sm transition-colors">Submit Request</button>
    </form>
</div>

@endsection
