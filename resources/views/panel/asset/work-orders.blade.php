@extends('panel.layout')
@section('title', 'Work Orders')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Work Orders</h1>
    <p class="text-sm text-gray-500 mt-0.5">Corrective and preventive maintenance tasks</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Work orders list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">WO No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Asset / Room</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Assignee</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($orders as $o)
                        @php
                            $typeColors = ['corrective' => 'red', 'preventive' => 'blue', 'inspection' => 'violet'];
                            $statusColors = ['open' => 'amber', 'in_progress' => 'blue', 'completed' => 'emerald', 'verified' => 'primary'];
                            $tc = $typeColors[$o->type] ?? 'gray';
                            $sc = $statusColors[$o->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs text-gray-700 bg-gray-100 px-2 py-0.5 rounded-md">{{ $o->wo_no }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-xs font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2 py-0.5 rounded-full capitalize">{{ $o->type }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-700">
                                {{ $o->asset?->name ?? ('Room '.$o->room?->number) ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-sm text-gray-600">{{ $o->assignee?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">
                                    {{ str_replace('_', ' ', $o->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <form method="POST" action="{{ route('panel.asset.work-orders.update', $o->id) }}">
                                    @csrf @method('PATCH')
                                    <select name="action" onchange="this.form.submit()"
                                            class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white text-gray-700 hover:border-primary-400 outline-none transition-all cursor-pointer">
                                        <option value="" disabled selected>Update</option>
                                        <option value="start">Start</option>
                                        <option value="complete">Complete</option>
                                        <option value="verify">Verify</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="flex flex-col items-center justify-center py-10">
                                    <p class="text-sm text-gray-400">No work orders yet.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- New WO form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">New Work Order</h2>
        </div>
        <form method="POST" action="{{ route('panel.asset.work-orders.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Type <span class="text-red-500">*</span></label>
                <select name="type" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="corrective">Corrective</option>
                    <option value="preventive">Preventive</option>
                    <option value="inspection">Inspection</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room ID</label>
                <input type="number" name="room_id" placeholder="Optional"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Asset ID</label>
                <input type="number" name="asset_id" placeholder="Optional"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description <span class="text-red-500">*</span></label>
                <textarea name="description" required rows="3" placeholder="Describe the issue or task…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Priority</label>
                <select name="priority"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="low">Low</option>
                    <option value="normal" selected>Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Create Work Order
            </button>
        </form>
    </div>

</div>

@endsection
