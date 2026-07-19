@extends('panel.layout')
@section('title', 'Purchase Requests')
@section('content')

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900">Purchase Requests</h1>
    <a href="{{ route('panel.inventory.pr.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">New PR</a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">PR #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Requested By</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Department</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Required</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($prs as $pr)
                @php $sc = ['draft'=>'gray','pending'=>'amber','approved'=>'emerald','rejected'=>'red','ordered'=>'blue'][$pr->status]??'gray'; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium"><a href="{{ route('panel.inventory.pr.show', $pr->id) }}" class="text-primary-600 hover:underline">{{ $pr->pr_number }}</a></td>
                    <td class="px-4 py-3.5 text-gray-700">{{ $pr->requester?->name }}</td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $pr->department }}</td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $pr->required_date?->format('d M Y') ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-center"><span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $pr->status }}</span></td>
                    <td class="px-4 py-3.5 text-right">
                        @if($pr->status === 'pending')
                        <form method="POST" action="{{ route('panel.inventory.pr.approve', $pr->id) }}" class="inline">@csrf<button class="text-xs text-emerald-600 hover:underline mr-2">Approve</button></form>
                        @endif
                        @if($pr->status === 'approved')
                        <a href="{{ route('panel.inventory.po.create', ['pr_id' => $pr->id]) }}" class="text-xs text-primary-600 hover:underline">Create PO</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-10 text-center text-sm text-gray-400">No purchase requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($prs->hasPages())<div class="px-5 py-3 border-t border-gray-100">{{ $prs->links() }}</div>@endif
</div>

@endsection
