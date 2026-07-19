@extends('panel.layout')
@section('title', 'Shift Schedule')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <h1 class="text-2xl font-bold text-gray-900">Shift Schedule</h1>
    <div class="flex items-center gap-2">
        <form method="GET" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date->toDateString() }}" class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
            <select name="view" class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                <option value="weekly" {{ $view === 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ $view === 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
            <button type="submit" class="bg-primary-600 text-white text-sm font-medium px-4 py-2 rounded-xl hover:bg-primary-700 transition-colors">View</button>
        </form>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide sticky left-0 bg-gray-50/80">Employee</th>
                    @foreach ($dates as $d)
                    <th class="px-2 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide min-w-[60px]">
                        <div>{{ $d->format('D') }}</div>
                        <div class="text-[10px] text-gray-400">{{ $d->format('d') }}</div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($employees as $emp)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-4 py-2.5 font-medium text-gray-800 text-xs sticky left-0 bg-white">{{ $emp->first_name }}</td>
                    @foreach ($dates as $d)
                    @php
                        $sch = $schedules->get($d->toDateString())?->firstWhere('employee_id', $emp->id);
                        $colors = ['morning' => 'yellow', 'afternoon' => 'blue', 'night' => 'indigo', 'off' => 'gray'];
                    @endphp
                    <td class="px-1 py-2 text-center">
                        <select onchange="this.form.submit()" form="shiftForm{{ $emp->id }}" name="shift_type[{{ $d->toDateString() }}]"
                                class="w-full text-[10px] font-medium rounded-md border border-gray-200 px-1 py-1 {{ $sch ? 'bg-'.($colors[$sch->shift_type]??'gray').'-50 text-'.($colors[$sch->shift_type]??'gray').'-700' : '' }}">
                            <option value="" {{ !$sch ? 'selected' : '' }}>—</option>
                            <option value="morning" {{ ($sch->shift_type ?? '') === 'morning' ? 'selected' : '' }}>AM</option>
                            <option value="afternoon" {{ ($sch->shift_type ?? '') === 'afternoon' ? 'selected' : '' }}>PM</option>
                            <option value="night" {{ ($sch->shift_type ?? '') === 'night' ? 'selected' : '' }}>Night</option>
                            <option value="off" {{ ($sch->shift_type ?? '') === 'off' ? 'selected' : '' }}>Off</option>
                        </select>
                        <form id="shiftForm{{ $emp->id }}" method="POST" action="{{ route('panel.hr.schedule.assign') }}" hidden>
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                            <input type="hidden" name="date" value="{{ $d->toDateString() }}">
                        </form>
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="{{ count($dates)+1 }}" class="py-10 text-center text-sm text-gray-400">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="text-xs text-gray-400 mt-2">Select shift type from dropdown to auto-save. Changes are submitted immediately.</p>

@endsection
