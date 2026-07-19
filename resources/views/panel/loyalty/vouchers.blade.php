@extends('panel.layout')
@section('title', 'Gift Vouchers')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Gift Vouchers</h1>
    <p class="text-sm text-gray-500 mt-0.5">Issue and manage redeemable gift vouchers for guests</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Vouchers table --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Code</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Face Value</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Balance</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Valid Until</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($vouchers as $v)
                        @php
                            $typeColors = ['amount' => 'primary', 'night' => 'blue', 'package' => 'violet', 'spa' => 'rose', 'fnb' => 'orange'];
                            $statusColors = ['active' => 'emerald', 'redeemed' => 'gray', 'expired' => 'red', 'cancelled' => 'red'];
                            $tc = $typeColors[$v->type] ?? 'gray';
                            $sc = $statusColors[$v->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-sm font-semibold text-gray-900 bg-gray-100 px-2 py-0.5 rounded-md">{{ $v->code }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2 py-0.5 rounded-full capitalize">{{ $v->type }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                                Rp {{ number_format($v->face_value, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm {{ $v->balance > 0 ? 'text-emerald-700 font-semibold' : 'text-gray-400' }}">
                                Rp {{ number_format($v->balance, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-600">{{ $v->valid_until?->format('d M Y') ?? '∞' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $v->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-gray-400">No vouchers issued yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($vouchers->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $vouchers->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Issue voucher form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Issue Voucher</h2>
        </div>
        <form method="POST" action="{{ route('panel.loyalty.vouchers.issue') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Type <span class="text-red-500">*</span></label>
                <select name="type" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="amount">Amount</option>
                    <option value="night">Night Stay</option>
                    <option value="package">Package</option>
                    <option value="spa">Spa</option>
                    <option value="fnb">F&B</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Face Value (Rp) <span class="text-red-500">*</span></label>
                <input type="number" step="1" name="face_value" required placeholder="500000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Valid Until</label>
                <input type="date" name="valid_until"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Recipient Email</label>
                <input type="email" name="issued_to_email" placeholder="guest@example.com"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Recipient Phone</label>
                <input type="tel" name="issued_to_phone" placeholder="+62812..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Message</label>
                <textarea name="message" rows="2" placeholder="Congratulations! Enjoy your stay…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Issue Voucher
            </button>
        </form>
    </div>

</div>

@endsection
