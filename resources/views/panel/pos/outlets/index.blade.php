@extends('panel.layout')
@section('title', 'POS Outlets')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">POS Outlets</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola outlet F&amp;B: restoran, bar, room service, minibar</p>
    </div>
    <a href="{{ route('panel.pos.outlets.create') }}"
       class="inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Outlet
    </a>
</div>

@if (session('success') || session('warning') || $errors->any())
<div class="mb-4 space-y-2">
    @if (session('success'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>@endif
    @if (session('warning'))<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl text-sm">{{ session('warning') }}</div>@endif
    @foreach ($errors->all() as $err)
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">{{ $err }}</div>
    @endforeach
</div>
@endif

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Outlet</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Meja</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Menu</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
        @forelse ($outlets as $o)
            <tr class="hover:bg-gray-50/60 transition-colors">
                <td class="px-5 py-3">
                    <p class="font-medium text-gray-900">{{ $o->name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $o->code }}</p>
                </td>
                <td class="px-5 py-3 text-gray-600 capitalize">{{ str_replace('_', ' ', $o->type) }}</td>
                <td class="px-5 py-3 text-center text-gray-600">{{ $o->tables_count }}</td>
                <td class="px-5 py-3 text-center text-gray-600">{{ $o->menu_items_count }}</td>
                <td class="px-5 py-3 text-center">
                    @if ($o->is_active)
                        <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Active</span>
                    @else
                        <span class="text-xs font-medium bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-2 text-xs">
                        <a href="{{ route('panel.pos.tables', $o->id) }}" class="text-primary-600 hover:text-primary-800 font-medium">Meja</a>
                        <a href="{{ route('panel.pos.outlets.edit', $o->id) }}" class="text-gray-500 hover:text-gray-700 font-medium">Edit</a>
                        <form method="POST" action="{{ route('panel.pos.outlets.toggle', $o->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-700 font-medium">{{ $o->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                        <form method="POST" action="{{ route('panel.pos.outlets.destroy', $o->id) }}" class="inline"
                              onsubmit="return confirm('Hapus outlet ini? Outlet dengan riwayat order hanya akan dinonaktifkan.')">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center">
                    <p class="text-sm text-gray-500">Belum ada outlet.</p>
                    <a href="{{ route('panel.pos.outlets.create') }}" class="text-sm text-primary-600 underline mt-1 inline-block">Buat outlet pertama</a>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($outlets->hasPages())
<div class="px-5 py-3 mt-2">{{ $outlets->links() }}</div>
@endif

@endsection
