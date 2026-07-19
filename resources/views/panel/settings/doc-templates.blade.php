@extends('panel.layout')
@section('title', 'Document Templates')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Document Templates</h1>
    <p class="text-sm text-gray-500 mt-0.5">HTML templates for folios, invoices, BEOs, and email confirmations</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Templates list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Locale</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Default</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($templates as $t)
                        @php
                            $typeColors = ['folio' => 'blue', 'invoice' => 'emerald', 'beo' => 'violet', 'contract' => 'amber', 'registration_card' => 'orange', 'email_confirmation' => 'primary'];
                            $tc = $typeColors[$t->type] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-800">{{ $t->name }}</td>
                            <td class="px-4 py-3.5">
                                <span class="text-xs font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2 py-0.5 rounded-full capitalize">
                                    {{ str_replace('_', ' ', $t->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">{{ $t->locale }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if ($t->is_default)
                                <svg class="w-4 h-4 text-emerald-500 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                @else
                                <span class="text-gray-200">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if ($t->is_active)
                                <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                                @else
                                <span class="text-xs font-medium bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-sm text-gray-400">No templates yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- New template form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">New Template</h2>
        </div>
        <form method="POST" action="{{ route('panel.settings.doc-templates.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Default Invoice ID"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Type <span class="text-red-500">*</span></label>
                    <select name="type" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="folio">Folio</option>
                        <option value="invoice">Invoice</option>
                        <option value="beo">BEO</option>
                        <option value="contract">Contract</option>
                        <option value="registration_card">Reg. Card</option>
                        <option value="email_confirmation">Email Confirmation</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Locale</label>
                    <input type="text" name="locale" value="id" placeholder="id / en"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Header HTML</label>
                <textarea name="header_html" rows="2" placeholder="<header>…</header>"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Body HTML <span class="text-red-500">*</span></label>
                <textarea name="body_html" rows="6" required
                          placeholder="Use &#123;&#123;guest_name&#125;&#125;, &#123;&#123;ref&#125;&#125;, &#123;&#123;total&#125;&#125;…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Footer HTML</label>
                <textarea name="footer_html" rows="2" placeholder="<footer>…</footer>"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <div class="flex items-center gap-2 p-3 bg-gray-50/60 rounded-xl border border-gray-100">
                <input type="checkbox" name="is_default" id="is_default_tpl" value="1"
                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-400">
                <label for="is_default_tpl" class="text-xs font-medium text-gray-700">Set as default for this type + locale</label>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Save Template
            </button>
        </form>
    </div>

</div>

@endsection
