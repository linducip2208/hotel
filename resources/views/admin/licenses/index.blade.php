@extends('admin.layout')

@section('title', 'Licenses')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Licenses</h1>
    <a href="{{ route('admin.licenses.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded">Create License</a>
</div>
<div class="bg-white rounded shadow border border-gray-100 p-4">
    @if(($events ?? collect())->isNotEmpty())
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left"><th class="p-2">ID</th><th class="p-2">Event</th><th class="p-2">Date</th><th class="p-2">Actions</th></tr></thead>
            <tbody>
            @foreach($events as $event)
                <tr class="border-b">
                    <td class="p-2">{{ $event->id }}</td>
                    <td class="p-2">{{ $event->event }}</td>
                    <td class="p-2">{{ $event->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="p-2"><a href="{{ route('admin.licenses.show', $event->id) }}" class="text-primary-600">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($events instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">{{ $events->links() }}</div>
        @endif
    @else
        <p class="text-gray-500 text-center py-12">No license events found.</p>
    @endif
</div>
@endsection
