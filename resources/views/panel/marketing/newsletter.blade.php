@extends('panel.layout')
@section('title', 'Newsletter')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Newsletter Subscriber</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola daftar email pelanggan untuk kampanye promosi</p>
    </div>
    <a href="{{ route('panel.marketing.newsletter.export', ['status' => $status]) }}"
       class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export CSV
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('panel.marketing.newsletter.index', ['status' => 'subscribed']) }}"
       class="bg-white rounded-2xl border {{ $status === 'subscribed' ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100' }} shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Aktif</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $counts['subscribed'] }}</p>
    </a>
    <a href="{{ route('panel.marketing.newsletter.index', ['status' => 'unsubscribed']) }}"
       class="bg-white rounded-2xl border {{ $status === 'unsubscribed' ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100' }} shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Berhenti</p>
        <p class="text-2xl font-bold text-rose-600 mt-1">{{ $counts['unsubscribed'] }}</p>
    </a>
    <a href="{{ route('panel.marketing.newsletter.index', ['status' => 'bounced']) }}"
       class="bg-white rounded-2xl border {{ $status === 'bounced' ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100' }} shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Bounced</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $counts['bounced'] }}</p>
    </a>
    <a href="{{ route('panel.marketing.newsletter.index', ['status' => 'all']) }}"
       class="bg-white rounded-2xl border {{ $status === 'all' ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100' }} shadow-card p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $counts['total'] }}</p>
    </a>
</div>

{{-- Add manually --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-3">Tambah Subscriber Manual</h2>
    <form method="POST" action="{{ route('panel.marketing.newsletter.store') }}" class="flex flex-wrap gap-3">
        @csrf
        <input type="email" name="email" required placeholder="email@contoh.com"
               class="flex-1 min-w-[220px] rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
        <input type="text" name="name" placeholder="Nama (opsional)"
               class="flex-1 min-w-[180px] rounded-xl border-gray-200 text-sm px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">Tambah</button>
    </form>
</div>

{{-- List --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Daftar Subscriber</h2>
        <form class="flex gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari email…"
                   class="rounded-lg border-gray-200 text-xs px-3 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Sumber</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Bergabung</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($subscribers as $s)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $s->email }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->name ?: '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $s->source }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->status === 'subscribed')
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif</span>
                        @elseif($s->status === 'bounced')
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Bounced</span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400"><span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>Berhenti</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $s->subscribed_at?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('panel.marketing.newsletter.destroy', $s->id) }}" onsubmit="return confirm('Hapus subscriber ini?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Belum ada subscriber.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $subscribers->links() }}
    </div>
</div>

@endsection
