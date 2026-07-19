@extends('panel.layout')
@section('title', 'Manajemen Role')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Manajemen Role</h2>
        <p class="text-sm text-slate-500">{{ $roles->count() }} role terdaftar</p>
    </div>
    <a href="{{ route('panel.settings.roles.create') }}" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Buat Role Baru
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Role</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Guard</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Permissions</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">User</th>
                <th class="text-right px-4 py-3 text-xs font-bold text-slate-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($roles as $role)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">{{ $role->name }}</p>
                            @if(in_array($role->name, ['super_owner', 'manager']))
                            <span class="text-[10px] text-amber-600 font-semibold">Sistem</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ $role->guard_name }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs font-mono font-bold text-indigo-600">{{ $role->permissions_count }}</span>
                    <span class="text-xs text-slate-400"> permission</span>
                </td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $role->users_count ?? 0 }} user</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('panel.settings.roles.edit', $role->id) }}" class="text-xs bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-lg hover:bg-indigo-100 font-medium">Edit</a>
                        @if(!in_array($role->name, ['super_owner', 'manager']))
                        <form method="POST" action="{{ route('panel.settings.roles.destroy', $role->id) }}" onsubmit="return confirm('Hapus role {{ $role->name }}?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-xs bg-rose-50 text-rose-600 px-2.5 py-1 rounded-lg hover:bg-rose-100 font-medium">Hapus</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
