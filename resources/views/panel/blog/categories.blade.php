@extends('panel.layout')

@section('title', 'Kategori Blog')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Kategori Blog</h2>
        <p class="text-sm text-slate-500 mt-0.5">Kelola kategori untuk mengorganisir artikel blog.</p>
    </div>
    <a href="{{ route('panel.blog.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 transition-colors">&larr; Kembali ke Artikel</a>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Add form --}}
    <div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-3">Tambah Kategori</h3>
            <form action="{{ route('panel.blog.categories.store') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Nama kategori..."
                       class="flex-1 px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" required maxlength="255">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors whitespace-nowrap">Tambah</button>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div>
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($categories as $cat)
                <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50/50 transition-colors group">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-900">{{ $cat->name }}</span>
                            <span class="text-xs text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">{{ $cat->posts_count }} artikel</span>
                        </div>
                        @if($cat->description)
                            <p class="text-xs text-slate-400 mt-0.5">{{ $cat->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="editCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description ?? '') }}')"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <form action="{{ route('panel.blog.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ addslashes($cat->name) }}?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="px-5 py-12 text-center text-slate-400 text-sm">Belum ada kategori.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editCategoryModal" style="display:none;" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Edit Kategori</h3>
        <form id="editCategoryForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
                <input type="text" name="name" id="editCatName"
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                <input type="text" name="description" id="editCatDesc" maxlength="500"
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div class="flex items-center gap-2 justify-end pt-2">
                <button type="button" onclick="document.getElementById('editCategoryModal').style.display='none'" class="text-sm font-medium text-slate-600 hover:text-slate-800 px-4 py-2">Batal</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl px-5 py-2 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(id, name, desc) {
    document.getElementById('editCategoryForm').action = '{{ route('panel.blog.categories.update', '_id_') }}'.replace('_id_', id);
    document.getElementById('editCatName').value = name;
    document.getElementById('editCatDesc').value = desc;
    document.getElementById('editCategoryModal').style.display = 'flex';
}
document.getElementById('editCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
@endsection
