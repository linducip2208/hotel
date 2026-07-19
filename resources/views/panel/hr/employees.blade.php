@extends('panel.layout')
@section('title', 'Employees')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
    <p class="text-sm text-gray-500 mt-0.5">Staff roster, positions, and employment details</p>
</div>

@if (session('status'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('status') }}
</div>
@endif

<div class="grid md:grid-cols-3 gap-5">

    {{-- Employee table --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">All Staff</h2>
                <span class="text-xs text-gray-400">{{ $employees->total() }} employees</span>
            </div>
            <div class="px-5 py-3 border-b border-gray-50 bg-gray-50/50">
                <form method="GET" class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, posisi, departemen..."
                           class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-3 py-2 text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </form>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($employees as $e)
                @php
                    $initials = collect(explode(' ', $e->full_name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                    $typeColors = ['permanent' => 'emerald', 'contract' => 'blue', 'daily' => 'amber', 'outsource' => 'gray'];
                    $tc = $typeColors[$e->employment_type ?? ''] ?? 'gray';
                @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('panel.hr.employees.show', $e->id) }}"
                               class="text-sm font-semibold text-gray-900 hover:text-primary-600 transition-colors">{{ $e->full_name }}</a>
                            @if (!$e->is_active)
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                            <span>{{ $e->position }}</span>
                            <span>·</span>
                            <span>{{ $e->department }}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-xs font-mono font-medium text-gray-700">Rp {{ number_format($e->basic_salary, 0, ',', '.') }}</div>
                        <div class="mt-0.5">
                            <span class="text-xs font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2 py-0.5 rounded-full capitalize">{{ $e->employment_type ?? 'staff' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <a href="{{ route('panel.hr.employees.show', $e->id) }}"
                           class="text-xs font-medium text-primary-600 bg-primary-50 px-2.5 py-1 rounded-lg hover:bg-primary-100 transition-colors">View</a>
                        <a href="{{ route('panel.hr.employees.edit', $e->id) }}"
                           class="text-xs font-medium text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg hover:bg-amber-100 transition-colors">Edit</a>
                        <form method="POST" action="{{ route('panel.hr.employees.destroy', $e->id) }}" onsubmit="return confirm('Hapus karyawan ini?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-xs font-medium text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 transition-colors">Hapus</button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <p class="text-sm text-gray-500">No employees yet</p>
                </div>
                @endforelse
            </div>
            @if ($employees->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $employees->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Add employee form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Add Employee</h2>
        </div>
        <form method="POST" action="{{ route('panel.hr.employees.store') }}" class="p-5 space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="Budi"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Santoso"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Position <span class="text-red-500">*</span></label>
                <input type="text" name="position" value="{{ old('position') }}" required placeholder="Front Desk Agent"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Department <span class="text-red-500">*</span></label>
                <input type="text" name="department" value="{{ old('department') }}" required placeholder="Front Office"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Join Date <span class="text-red-500">*</span></label>
                <input type="date" name="joined_at" value="{{ old('joined_at') }}" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Basic Salary (Rp) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary') }}" required placeholder="4500000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Employment Type</label>
                <select name="employment_type"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="permanent">Permanent</option>
                    <option value="contract">Contract</option>
                    <option value="daily">Daily</option>
                    <option value="outsource">Outsource</option>
                </select>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Add Employee
            </button>
        </form>
    </div>

</div>

@endsection
