@extends('admin.layout')

@section('title', 'Audit Log')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Audit Log</h1>
    <a href="{{ route('admin.system.flags') }}" class="text-primary-600">Back to System</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-4">
    @if(($logs ?? collect())->isNotEmpty())
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left"><th class="p-2">Date</th><th class="p-2">Action</th><th class="p-2">User</th><th class="p-2">Details</th></tr></thead>
            <tbody>
            @foreach($logs as $log)
                <tr class="border-b">
                    <td class="p-2">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="p-2">{{ $log->action }}</td>
                    <td class="p-2">{{ $log->user_id ?? 'system' }}</td>
                    <td class="p-2 text-xs text-gray-500">{{ Str::limit(json_encode($log->metadata), 80) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">{{ $logs->links() }}</div>
        @endif
    @else
        <p class="text-gray-500 text-center py-12">No audit log entries found.</p>
    @endif
</div>
@endsection
