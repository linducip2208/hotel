@extends('panel.layout')
@section('title', 'Performance Reviews')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Performance Reviews</h1>
    <a href="{{ route('panel.hr.performance.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">New Review</a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Employee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reviewer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Rating</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($reviews as $r)
                @php $sc = ['draft' => 'gray', 'completed' => 'emerald', 'acknowledged' => 'blue'][$r->status] ?? 'gray'; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-800">{{ $r->employee?->full_name }}</td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $r->reviewer?->name }}</td>
                    <td class="px-4 py-3.5 text-gray-600 text-xs">{{ $r->period_start?->format('d M') }} – {{ $r->period_end?->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="font-bold text-sm">{{ $r->overall_rating }}/5</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $r->status }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center gap-1 justify-end">
                            <a href="{{ route('panel.hr.performance.show', $r->id) }}" class="text-xs font-medium text-primary-600 hover:underline">View</a>
                            <a href="{{ route('panel.hr.performance.edit', $r->id) }}" class="text-xs font-medium text-amber-600 hover:underline">Edit</a>
                            @if($r->status === 'completed')
                            <form method="POST" action="{{ route('panel.hr.performance.acknowledge', $r->id) }}" class="inline">
                                @csrf
                                <button class="text-xs font-medium text-emerald-600 hover:underline">Acknowledge</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('panel.hr.performance.destroy', $r->id) }}" onsubmit="return confirm('Hapus review ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-xs font-medium text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-10 text-center text-sm text-gray-400">No reviews yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($reviews->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $reviews->links() }}
    </div>
    @endif
</div>

@endsection
