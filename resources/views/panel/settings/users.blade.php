@extends('panel.layout')
@section('title', 'Users & Roles')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Users & Roles</h1>
    <p class="text-sm text-gray-500 mt-0.5">Manage staff accounts and access permissions</p>
</div>

@if (session('temp_password'))
<div class="mb-4 bg-emerald-50 border-2 border-emerald-300 rounded-2xl px-5 py-4">
    <p class="text-sm font-semibold text-emerald-800">Password sementara untuk {{ session('temp_password_user') }}:</p>
    <p class="text-lg font-mono font-bold text-emerald-900 mt-1 select-all">{{ session('temp_password') }}</p>
    <p class="text-xs text-emerald-700 mt-1">Hanya ditampilkan sekali. Berikan ke staff dan minta ganti password setelah login.</p>
</div>
@endif

@if (session('success') || session('warning') || $errors->any())
<div class="mb-4 space-y-2">
    @if (session('success'))<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>@endif
    @if (session('warning'))<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl text-sm">{{ session('warning') }}</div>@endif
    @foreach ($errors->all() as $err)
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">{{ $err }}</div>
    @endforeach
</div>
@endif

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
                <div class="px-5 py-3.5 hover:bg-gray-50/60 transition-colors"
                     x-data="{ editing: false }">
                    <div class="flex items-center gap-4">
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
                        <div class="flex items-center gap-1.5 shrink-0">
                            <div class="w-2 h-2 rounded-full {{ ($u->is_active ?? true) ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                            <button type="button" @click="editing = !editing" class="text-xs text-primary-600 hover:text-primary-800 font-medium px-2">Edit</button>
                        </div>
                    </div>

                    {{-- Edit panel --}}
                    <div x-show="editing" x-cloak class="mt-3 pt-3 border-t border-gray-100">
                        <form method="POST" action="{{ route('panel.settings.users.update', $u->id) }}" class="grid md:grid-cols-4 gap-2 items-end">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama</label>
                                <input type="text" name="name" required maxlength="100" value="{{ $u->name }}"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm outline-none focus:border-primary-400">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Phone</label>
                                <input type="text" name="phone" maxlength="30" value="{{ $u->phone }}"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm outline-none focus:border-primary-400">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Role</label>
                                <select name="role" required class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm outline-none focus:border-primary-400">
                                    @foreach ($roles as $r)
                                        <option value="{{ $r->name }}" @selected($roleNames->contains($r->name))>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-3 py-2 rounded-lg">Simpan</button>
                        </form>

                        <div class="flex items-center gap-3 mt-3 text-xs">
                            <form method="POST" action="{{ route('panel.settings.users.toggle-active', $u->id) }}" class="inline"
                                  onsubmit="return confirm('{{ ($u->is_active ?? true) ? 'Nonaktifkan akun ini? Semua sesi akan dicabut.' : 'Aktifkan kembali akun ini?' }}')">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-gray-800 font-medium">
                                    {{ ($u->is_active ?? true) ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <span class="text-gray-200">|</span>
                            <form method="POST" action="{{ route('panel.settings.users.reset-password', $u->id) }}" class="inline"
                                  onsubmit="return confirm('Reset password akun ini? Password sementara akan ditampilkan sekali.')">
                                @csrf
                                <button type="submit" class="text-orange-600 hover:text-orange-800 font-medium">Reset Password</button>
                            </form>
                        </div>
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
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit" x-data="{ submitting: false }">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Add Staff Account</h2>
        </div>
        <form method="POST" action="{{ route('panel.settings.users.store') }}" @submit="submitting = true" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Budi Santoso"
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
            <button type="submit" :disabled="submitting"
                    class="w-full bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                <span x-show="!submitting">Create Account</span>
                <span x-show="submitting" x-cloak>Menyimpan…</span>
            </button>
        </form>
    </div>

</div>

@endsection
