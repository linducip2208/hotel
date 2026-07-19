@extends('panel.layout')
@section('title', 'Buat Role Baru')
@section('content')
<div class="mb-6">
    <a href="{{ route('panel.settings.roles.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali</a>
    <h2 class="text-xl font-bold text-slate-800 mt-1">Buat Role Baru</h2>
    <p class="text-sm text-slate-500">Tentukan nama role dan pilih permission yang diberikan</p>
</div>

<form method="POST" action="{{ route('panel.settings.roles.store') }}" class="max-w-3xl">
    @csrf
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm mb-4">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Role</label>
        <input name="name" required class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5" placeholder="contoh: night_auditor">
        <p class="text-xs text-slate-400 mt-1">Gunakan snake_case, tanpa spasi. Contoh: front_office, sales_marketing.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Permissions</h3>
            <p class="text-xs text-slate-500">Centang permission yang diberikan ke role ini</p>
        </div>

        @foreach($permissionGroups as $group => $perms)
        <div class="px-6 py-4 border-b border-slate-100 last:border-b-0">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $group }}</p>
                <button type="button" onclick="toggleGroup(this)" data-group="{{ Str::slug($group) }}" class="text-xs text-indigo-600 hover:underline font-medium">Select All</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2" id="group-{{ Str::slug($group) }}">
                @foreach($perms as $perm)
                <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer hover:text-slate-800 py-1">
                    <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <code class="text-[11px] bg-slate-50 px-1 py-0.5 rounded">{{ $perm }}</code>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4 flex gap-3">
        <button class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700">Simpan Role</button>
        <a href="{{ route('panel.settings.roles.index') }}" class="bg-slate-100 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-200">Batal</a>
    </div>
</form>

<script>
function toggleGroup(btn) {
    const groupId = btn.dataset.group;
    const container = document.getElementById('group-' + groupId);
    const checkboxes = container.querySelectorAll('input[type="checkbox"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    btn.textContent = !allChecked ? 'Deselect All' : 'Select All';
}
</script>
@endsection
