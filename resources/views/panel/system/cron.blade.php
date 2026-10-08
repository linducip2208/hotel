@extends('panel.layout')
@section('title', 'Cron Task Manager')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Cron Task Manager</h1>
    <p class="text-sm text-gray-500 mt-0.5">Pantau dan jalankan tugas otomatis (scheduler) aplikasi</p>
</div>

@if($lastHeartbeat)
<div class="mb-6 bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-sm text-emerald-800 flex items-center gap-2">
    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
    Heartbeat deployment terakhir: {{ $lastHeartbeat->created_at?->diffForHumans() }}
</div>
@endif

<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700">{{ $tasks->count() }} Tugas Terjadwal</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tugas</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Frekuensi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Cron</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Berikutnya</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($tasks as $t)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-900">
                        {{ $t['description'] }}
                        @if($t['signature'])
                        <span class="block text-xs font-mono text-gray-400 mt-0.5">{{ $t['signature'] }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $t['human'] }}</td>
                    <td class="px-4 py-3.5 font-mono text-xs text-indigo-600">{{ $t['expression'] }}</td>
                    <td class="px-4 py-3.5 text-gray-500">{{ $t['next_run'] }} <span class="text-gray-300">{{ $t['timezone'] }}</span></td>
                    <td class="px-4 py-3.5 text-right">
                        @if($t['signature'])
                        <form method="POST" action="{{ route('panel.system.cron.run') }}" onsubmit="return confirm('Jalankan tugas ini sekarang?')" class="inline">
                            @csrf
                            <input type="hidden" name="command" value="{{ $t['signature'] }}">
                            <button class="px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">Jalankan</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Tidak ada tugas terjadwal terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
