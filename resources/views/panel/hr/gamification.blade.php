@extends('panel.layout')
@section('title', 'Gamifikasi Karyawan')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Gamifikasi Karyawan</h1>
        <p class="text-sm text-gray-500 mt-0.5">Leaderboard, lencana, dan poin karyawan</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="?period=weekly" class="px-3 py-1.5 text-xs font-semibold rounded-lg {{ $period === 'weekly' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition-colors">
            Mingguan
        </a>
        <a href="?period=monthly" class="px-3 py-1.5 text-xs font-semibold rounded-lg {{ $period === 'monthly' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition-colors">
            Bulanan
        </a>
    </div>
</div>

@if (session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 4000)"
     class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="grid lg:grid-cols-3 gap-5">

    {{-- Leaderboard --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Papan Peringkat — {{ $period === 'weekly' ? 'Minggu Ini' : 'Bulan Ini' }}</h2>
            </div>

            {{-- Top 3 Podium --}}
            @if(count($leaderboard) >= 3)
            <div class="px-6 py-6 flex items-end justify-center gap-4 bg-gradient-to-b from-amber-50/50 to-white">
                @php
                    $podiumColors = [1 => 'amber', 2 => 'gray', 3 => 'orange'];
                    $podiumHeights = [1 => 'h-24', 2 => 'h-16', 3 => 'h-12'];
                    $podiumOrder = [2, 1, 3];
                @endphp
                @foreach($podiumOrder as $rank)
                    @php $entry = $leaderboard[$rank-1] ?? null; @endphp
                    @if($entry)
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 rounded-full bg-{{ $podiumColors[$rank] }}-100 text-{{ $podiumColors[$rank] }}-700 flex items-center justify-center text-lg font-bold mb-2 shadow-sm">
                            {{ strtoupper(substr($entry['name'], 0, 1)) }}
                        </div>
                        <span class="text-xs font-semibold text-gray-900 text-center truncate w-20">{{ $entry['name'] }}</span>
                        <span class="text-[11px] text-gray-400">{{ $entry['department'] }}</span>
                        <div class="mt-1 text-sm font-bold text-{{ $podiumColors[$rank] }}-600">{{ $entry['points'] }} pts</div>
                        <div class="mt-2 w-20 {{ $podiumHeights[$rank] }} bg-{{ $podiumColors[$rank] }}-200 rounded-t-lg flex items-end justify-center pb-1">
                            <span class="text-2xl font-black text-{{ $podiumColors[$rank] }}-600">{{ $rank }}</span>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
            @endif

            {{-- Full leaderboard table --}}
            <div class="divide-y divide-gray-50">
                @forelse($leaderboard as $entry)
                <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/60 transition-colors">
                    <div class="w-8 h-8 rounded-full {{ $entry['rank'] <= 3 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center text-xs font-bold shrink-0">
                        {{ $entry['rank'] }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900">{{ $entry['name'] }}</div>
                        <div class="text-xs text-gray-400">{{ $entry['department'] }}</div>
                    </div>
                    @if(!empty($entry['badges']))
                    <div class="flex items-center gap-1">
                        @foreach($entry['badges'] as $b)
                        <span class="text-xs bg-violet-50 text-violet-600 px-2 py-0.5 rounded-full" title="{{ $b }}">{{ $b }}</span>
                        @endforeach
                    </div>
                    @endif
                    <div class="text-sm font-bold text-primary-600 shrink-0">{{ $entry['points'] }} pts</div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <p class="text-sm text-gray-500">Belum ada data leaderboard</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sidebar: Badge management + Award Points --}}
    <div class="space-y-5">

        {{-- Award Points --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Berikan Poin</h2>
            </div>
            <form method="POST" action="{{ route('panel.hr.gamification.points.award') }}" class="p-5 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Karyawan</label>
                    <select name="employee_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="">Pilih karyawan...</option>
                        @foreach(\App\Models\Employee::where('property_id', app('current_property')->id)->where('is_active', true)->get() as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->department }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Poin</label>
                    <input type="number" name="points" value="10" min="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Alasan</label>
                    <input type="text" name="reason" required placeholder="Bersihkan 5 kamar..." class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori</label>
                    <select name="category" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="hk">Housekeeping</option>
                        <option value="fo">Front Office</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                    Berikan Poin
                </button>
            </form>
        </div>

        {{-- Badge Configuration --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Kelola Lencana</h2>
            </div>

            {{-- Badge list --}}
            <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                @forelse($badges as $badge)
                <div class="flex items-center gap-3 px-5 py-2.5">
                    @php
                        $iconMap = ['star' => '⭐', 'fire' => '🔥', 'crown' => '👑', 'bolt' => '⚡', 'heart' => '❤️'];
                        $colorMap = ['amber' => 'bg-amber-50 text-amber-700', 'emerald' => 'bg-emerald-50 text-emerald-700', 'violet' => 'bg-violet-50 text-violet-700', 'rose' => 'bg-rose-50 text-rose-700', 'sky' => 'bg-sky-50 text-sky-700'];
                    @endphp
                    <span class="text-lg">{{ $iconMap[$badge->icon] ?? '⭐' }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900">{{ $badge->name }}</div>
                        <div class="text-xs text-gray-400">{{ $badge->criteria }} &ge; {{ $badge->threshold }}</div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $colorMap[$badge->color] ?? 'bg-gray-50 text-gray-500' }}">{{ $badge->category }}</span>
                    <form method="POST" action="{{ route('panel.hr.gamification.badges.destroy', $badge->id) }}" onsubmit="return confirm('Hapus lencana ini?')" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-red-400 hover:text-red-600 text-xs">&times;</button>
                    </form>
                </div>
                @empty
                <div class="px-5 py-4 text-xs text-gray-400">Belum ada lencana</div>
                @endforelse
            </div>

            {{-- Create badge form --}}
            <form method="POST" action="{{ route('panel.hr.gamification.badges.store') }}" class="p-5 space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama</label>
                        <input type="text" name="name" required placeholder="Cleaning Master" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ikon</label>
                        <select name="icon" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                            <option value="star">⭐ Bintang</option>
                            <option value="fire">🔥 Api</option>
                            <option value="crown">👑 Mahkota</option>
                            <option value="bolt">⚡ Petir</option>
                            <option value="heart">❤️ Hati</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Warna</label>
                        <select name="color" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                            <option value="amber">Amber</option>
                            <option value="emerald">Emerald</option>
                            <option value="violet">Violet</option>
                            <option value="rose">Rose</option>
                            <option value="sky">Sky</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dept.</label>
                        <select name="category" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                            <option value="hk">Housekeeping</option>
                            <option value="fo">Front Office</option>
                            <option value="all">Semua</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kriteria</label>
                        <select name="criteria" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                            <option value="rooms_cleaned">Kamar Dibersihkan</option>
                            <option value="perfect_scores">Skor Sempurna</option>
                            <option value="total_points">Total Poin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Threshold</label>
                        <input type="number" name="threshold" value="10" min="1" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold py-2 rounded-xl shadow-sm transition-colors">
                    Tambah Lencana
                </button>
            </form>
        </div>

        {{-- Recent Points --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Poin Terbaru</h2>
            </div>
            <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                @forelse($recentPoints as $pt)
                <div class="flex items-center gap-3 px-5 py-2.5">
                    <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-[10px] font-bold shrink-0">
                        +{{ $pt->points }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-gray-900">{{ $pt->employee?->full_name ?? 'Unknown' }}</div>
                        <div class="text-xs text-gray-400">{{ $pt->reason }}</div>
                    </div>
                    <div class="text-[11px] text-gray-400">{{ $pt->earned_at->diffForHumans() }}</div>
                </div>
                @empty
                <div class="px-5 py-4 text-xs text-gray-400">Belum ada poin</div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
