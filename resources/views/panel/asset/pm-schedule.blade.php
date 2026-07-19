@extends('panel.layout')
@section('title', 'P.M. Scheduler')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Preventive Maintenance Scheduler</h1>
        <p class="text-sm text-gray-500 mt-0.5">Jadwal perawatan aset otomatis</p>
    </div>
    <div class="flex items-center gap-2">
        @if($overdueCount > 0)
        <span class="bg-rose-50 text-rose-700 text-xs font-semibold px-3 py-1.5 rounded-lg">{{ $overdueCount }} overdue</span>
        @endif
        <form method="POST" action="{{ route('panel.asset.pm.schedule') }}">
            @csrf
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors">
                Auto-Schedule
            </button>
        </form>
    </div>
</div>

@if (session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 4000)"
     class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Due This Week --}}
<div class="mb-5">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 bg-amber-50/50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-amber-800">Jatuh Tempo Minggu Ini ({{ count($dueThisWeek) }})</h2>
        </div>
        @if(count($dueThisWeek) > 0)
        <div class="divide-y divide-gray-50">
            @foreach($dueThisWeek as $task)
            @php
                $isOverdue = \Carbon\Carbon::parse($task['next_due_at'])->isPast();
            @endphp
            <div class="flex items-center gap-4 px-5 py-3 {{ $isOverdue ? 'bg-rose-50/30' : '' }}">
                <div class="w-2 h-2 rounded-full shrink-0 {{ $isOverdue ? 'bg-rose-500' : 'bg-amber-500' }}"></div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-900">{{ $task['task_name'] }}</div>
                    <div class="text-xs text-gray-400">{{ $task['asset']['name'] ?? 'Aset' }} &middot; {{ $task['frequency'] }}</div>
                </div>
                <div class="text-xs {{ $isOverdue ? 'text-rose-600 font-semibold' : 'text-amber-600' }}">
                    {{ \Carbon\Carbon::parse($task['next_due_at'])->format('d M Y') }}
                </div>
                <button onclick="openCompleteModal({{ $task['id'] }}, '{{ $task['task_name'] }}', '{{ $task['checklist'] ?? '' }}')"
                        class="text-xs font-medium bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-lg hover:bg-emerald-100 transition-colors">
                    Selesaikan
                </button>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-6 text-center text-xs text-gray-400">Semua PM task up-to-date</div>
        @endif
    </div>
</div>

{{-- All Schedules --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Semua Jadwal PM</h2>
        <span class="text-xs text-gray-400">{{ $allSchedules->total() }} jadwal</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50 text-left">
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aset</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tugas</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Frekuensi</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Terakhir</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($allSchedules as $s)
                @php
                    $dueDate = $s->next_due_at;
                    $isOverdue = $dueDate && $dueDate->isPast();
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr($s->asset->name ?? 'A', 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $s->asset->name ?? 'Aset #'.$s->asset_id }}</div>
                                <div class="text-xs text-gray-400">{{ $s->asset->category ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-700">{{ $s->task_name }}</td>
                    <td class="px-5 py-3">
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $s->frequency }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $s->last_done_at?->format('d M Y') ?? '-' }}</td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-semibold {{ $isOverdue ? 'text-rose-600' : 'text-gray-700' }}">
                            {{ $dueDate?->format('d M Y') ?? '-' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @if($isOverdue)
                        <span class="text-xs bg-rose-50 text-rose-700 px-2 py-0.5 rounded-full font-semibold">Overdue</span>
                        @elseif($s->is_active)
                        <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Aktif</span>
                        @else
                        <span class="text-xs bg-gray-100 text-gray-400 px-2 py-0.5 rounded-full">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1">
                            <button onclick="openCompleteModal({{ $s->id }}, '{{ $s->task_name }}', '{{ $s->checklist ?? '' }}')"
                                    class="text-xs font-medium bg-emerald-50 text-emerald-600 px-2 py-1 rounded-lg hover:bg-emerald-100 transition-colors">
                                Selesai
                            </button>
                            <form method="POST" action="{{ route('panel.asset.pm.toggle', $s->id) }}" class="inline">
                                @csrf
                                <button class="text-xs text-gray-400 hover:text-gray-600 px-1.5 py-1">{{ $s->is_active ? '⏸' : '▶' }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-400">Belum ada jadwal PM. Klik "Auto-Schedule" untuk membuat otomatis.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($allSchedules->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $allSchedules->links() }}
    </div>
    @endif
</div>

{{-- History --}}
<div class="mt-5 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Riwayat PM (20 Terakhir)</h2>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($history as $log)
        <div class="flex items-center gap-4 px-5 py-2.5">
            <div class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></div>
            <div class="flex-1 min-w-0">
                <div class="text-sm text-gray-900">{{ $log['schedule']['task_name'] ?? 'Task' }} — {{ $log['schedule']['asset']['name'] ?? 'Aset' }}</div>
                <div class="text-xs text-gray-400">oleh {{ $log['performed_by']['name'] ?? 'Sistem' }}</div>
            </div>
            <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($log['performed_at'])->format('d M Y') }}</div>
        </div>
        @empty
        <div class="px-5 py-4 text-xs text-gray-400">Belum ada riwayat PM</div>
        @endforelse
    </div>
</div>

{{-- Complete Task Modal --}}
<div id="completeModal" x-data="{ open: false, taskId: null, taskName: '' }"
     x-show="open" x-cloak
     x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open=false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Selesaikan PM Task</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="taskName"></p>
            <form method="POST" :action="'/panel/asset/pm/complete/' + taskId">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all" placeholder="Catatan hasil maintenance..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Biaya (Rp)</label>
                        <input type="number" name="cost" step="0.01" value="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-5">
                    <button type="button" @click="open=false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">Selesaikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCompleteModal(id, name, checklist) {
    const modal = document.getElementById('completeModal');
    modal.__x.$data.taskId = id;
    modal.__x.$data.taskName = name;
    modal.__x.$data.open = true;
}
</script>

@endsection
