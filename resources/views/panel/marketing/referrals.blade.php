@extends('panel.layout')
@section('title', 'Program Referral')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Program Referral</h1>
        <p class="text-sm text-gray-500 mt-0.5">Word-of-mouth rewards untuk tamu yang mereferensikan teman</p>
    </div>
    <div class="flex items-center gap-2">
        <button data-modal="generateModal"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Generate Kode
        </button>
        <a href="{{ route('panel.marketing.referrals.settings') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900 px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066"/></svg>
            Pengaturan
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Kode Aktif</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['active_codes'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total Referral</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['total_referrals'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Selesai</p>
        <p class="text-2xl font-bold text-teal-600 mt-1">{{ $stats['total_completed'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Pending</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['total_pending'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total Reward</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($stats['total_rewards'] ?? 0, 0, ',', '.') }}</p>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-6">
    {{-- Top Referrers --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
        <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Top Referrers</h2>
        </div>
        <div class="p-5">
            @if(empty($topReferrers) || count($topReferrers) === 0)
            <p class="text-sm text-gray-400 text-center py-6">Belum ada referrer.</p>
            @else
            <div class="space-y-3">
                @foreach($topReferrers as $i => $tr)
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $tr['owner_guest']['full_name'] ?? $tr['owner_guest']['first_name'] ?? 'Guest #'.$tr['id'] }}</p>
                        <p class="text-xs text-gray-400">{{ $tr['total_referrals'] }} referral · Rp {{ number_format($tr['total_rewards_earned'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Recent Referrals --}}
    <div class="md:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
        <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Referral Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Referrer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Referred</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Reward</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($recentReferrals as $r)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-2.5 text-sm text-gray-700">{{ $r->referrerGuest?->full_name ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-sm text-gray-700">{{ $r->referredGuest?->full_name ?? '-' }}</td>
                        <td class="px-4 py-2.5">
                            @if($r->status === 'completed')
                            <span class="inline-flex items-center gap-1 text-xs text-emerald-700 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Selesai</span>
                            @elseif($r->status === 'cancelled')
                            <span class="text-xs text-gray-400">Dibatalkan</span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs text-amber-700 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-sm text-gray-700">Rp {{ number_format($r->reward_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-400">{{ $r->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-10 text-center text-sm text-gray-400">Belum ada aktivitas referral.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Codes table --}}
<div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Semua Kode</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Owner</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Referrer Reward</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Referee Disc</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Uses</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($codes as $c)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-sm font-semibold text-gray-900 bg-gray-100 px-2 py-0.5 rounded-md">{{ $c->code }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $c->ownerGuest?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-emerald-700">
                        Rp {{ number_format($c->referrer_reward_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right text-sm text-indigo-700 font-medium">
                        {{ $c->referee_discount_pct }}%
                    </td>
                    <td class="px-4 py-3.5 text-right text-sm text-gray-700 tabular-nums">
                        {{ $c->uses_count }}<span class="text-gray-400">/{{ $c->uses_limit ?? '∞' }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($c->is_active)
                        <span class="inline-flex items-center gap-1 text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                        @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">Inactive</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-10 text-center text-sm text-gray-400">No referral codes yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($codes->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $codes->links() }}</div>
    @endif
</div>

{{-- Generate modal --}}
<div id="generateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Generate Referral Code</h3>
            <button onclick="document.getElementById('generateModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('panel.marketing.referrals.generate') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih Tamu</label>
                <select name="guest_id" required class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Pilih Tamu --</option>
                    @foreach($guests as $g)
                    <option value="{{ $g->id }}">{{ $g->full_name }} ({{ $g->email ?? $g->phone }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">Batal</button>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">Generate</button>
            </div>
        </form>
    </div>
</div>

{{-- Generate code form --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden mt-6">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700">Buat Kode Baru</h2>
    </div>
    <form method="POST" action="{{ route('panel.marketing.referrals.store') }}" class="p-5">
        @csrf
        <div class="grid md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Owner Guest ID <span class="text-red-500">*</span></label>
                <input type="number" name="owner_guest_id" required placeholder="Guest ID"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Referrer Reward (Rp)</label>
                <input type="number" step="0.01" name="referrer_reward_amount" placeholder="50000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Referee Discount %</label>
                <input type="number" step="0.01" name="referee_discount_pct" placeholder="10"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
        </div>
        <div class="grid md:grid-cols-4 gap-4 mt-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Uses Limit</label>
                <input type="number" name="uses_limit" placeholder="Unlimited"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 rounded-xl shadow-sm transition-colors">
                    Generate Code
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById(btn.dataset.modal).classList.remove('hidden');
    });
});
</script>

@endsection
