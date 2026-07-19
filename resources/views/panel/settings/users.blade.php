@extends('panel.layout')
@section('title', 'Users & Roles')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Users & Roles</h1>
    <p class="text-sm text-gray-500 mt-0.5">Manage staff accounts and access permissions</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- User list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Staff Accounts</h2>
                <span class="text-xs text-gray-400">{{ $users->total() }} total</span>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($users as $u)
                @php
                    $initials = collect(explode(' ', $u->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                    $roleNames = $u->roles->pluck('name');
                @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">{{ $u->name }}</span>
                            @if (!($u->is_active ?? true))
                            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Inactive</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400 truncate">{{ $u->email }}</div>
                    </div>
                    <div class="flex flex-wrap gap-1 justify-end max-w-[160px]">
                        @foreach ($roleNames as $role)
                        <span class="text-xs font-medium bg-primary-50 text-primary-700 px-2 py-0.5 rounded-full">{{ $role }}</span>
                        @endforeach
                    </div>
                    <div class="flex items-center shrink-0">
                        <div class="w-2 h-2 rounded-full {{ ($u->is_active ?? true) ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <p class="text-sm text-gray-500">No staff accounts yet</p>
                </div>
                @endforelse
            </div>
            @if ($users->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Add user form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Add Staff Account</h2>
        </div>
        <form method="POST" action="{{ route('panel.settings.users.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Budi Santoso"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="budi@hotel.com"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="10" placeholder="Min. 10 characters"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Role <span class="text-red-500">*</span></label>
                <select name="role" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">— select role —</option>
                    @foreach ($roles as $r)
                    <option value="{{ $r->name }}" @selected(old('role') === $r->name)>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Create Account
            </button>
        </form>
    </div>

</div>

@endsection
