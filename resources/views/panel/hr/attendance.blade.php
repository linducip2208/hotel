@extends('panel.layout')
@section('title', 'Attendance')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Attendance</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
    <form method="GET">
        <input type="date" name="date" value="{{ $date }}"
               class="rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
    </form>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Attendance log --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-50">
                @forelse ($logs as $l)
                @php
                    $statusColors = ['present' => 'emerald', 'absent' => 'red', 'sick' => 'amber', 'leave' => 'blue', 'late' => 'orange'];
                    $sc = $statusColors[$l->status] ?? 'gray';
                    $initials = collect(explode(' ', $l->employee?->full_name ?? 'E'))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-{{ $sc }}-100 text-{{ $sc }}-700 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900">{{ $l->employee?->full_name }}</div>
                        <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                            @if ($l->clock_in)
                            <span>In: <span class="font-mono text-gray-700">{{ optional($l->clock_in)->format('H:i') }}</span></span>
                            @endif
                            @if ($l->clock_out)
                            <span>Out: <span class="font-mono text-gray-700">{{ optional($l->clock_out)->format('H:i') }}</span></span>
                            @endif
                            @if ($l->overtime_minutes > 0)
                            <span class="text-amber-600 font-medium">+{{ $l->overtime_minutes }}m OT</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize shrink-0">{{ $l->status }}</span>
                    <form method="POST" action="{{ route('panel.hr.attendance.destroy', $l->id) }}" onsubmit="return confirm('Hapus catatan kehadiran ini?')" class="shrink-0 ml-2">
                        @csrf @method('DELETE')
                        <button class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-lg hover:bg-red-100 transition-colors">Hapus</button>
                    </form>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <p class="text-sm text-gray-500">No attendance records for this date.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Clock form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Log Attendance</h2>
        </div>
        <form method="POST" action="{{ route('panel.hr.attendance.clock') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Employee <span class="text-red-500">*</span></label>
                <select name="employee_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">— select —</option>
                    @foreach ($employees as $e)
                    <option value="{{ $e->id }}">{{ $e->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" value="{{ $date }}" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Clock In</label>
                    <input type="datetime-local" name="clock_in"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Clock Out</label>
                    <input type="datetime-local" name="clock_out"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status <span class="text-red-500">*</span></label>
                <select name="status" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="sick">Sick</option>
                    <option value="leave">Leave</option>
                    <option value="late">Late</option>
                </select>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Save Record
            </button>
        </form>
    </div>

</div>

@endsection
