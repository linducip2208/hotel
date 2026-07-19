@extends('panel.layout')
@section('title', 'Leave Requests')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Leave Requests</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage employee leave applications</p>
    </div>
    <a href="{{ route('panel.hr.leave.create') }}"
       class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Request
    </a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Employee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Dates</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Days</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($leaves as $l)
                @php $sc = ['pending' => 'amber', 'approved' => 'emerald', 'rejected' => 'red', 'cancelled' => 'gray'][$l->status] ?? 'gray'; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-800">{{ $l->employee?->full_name }}</td>
                    <td class="px-4 py-3.5"><span class="text-xs font-medium bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full capitalize">{{ $l->type }}</span></td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $l->start_date?->format('d M') }} – {{ $l->end_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-center font-semibold">{{ $l->total_days }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $l->status }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center gap-1 justify-end">
                            @if($l->status === 'pending')
                            <form method="POST" action="{{ route('panel.hr.leave.approve', $l->id) }}" class="inline">
                                @csrf
                                <button class="text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg transition-colors">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('panel.hr.leave.reject', $l->id) }}" class="inline">
                                @csrf
                                <button class="text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 px-2.5 py-1.5 rounded-lg transition-colors">Reject</button>
                            </form>
                            @endif
                            <a href="{{ route('panel.hr.leave.balance', $l->employee_id) }}" class="text-xs text-primary-600 hover:underline">Balance</a>
                            <form method="POST" action="{{ route('panel.hr.leave.destroy', $l->id) }}" onsubmit="return confirm('Hapus cuti ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 px-2.5 py-1.5 rounded-lg transition-colors">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-10 text-center text-sm text-gray-400">No leave requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $leaves->links() }}</div>
    @endif
</div>

@endsection
