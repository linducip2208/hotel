@extends('panel.layout')
@section('title', 'Message Templates')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Message Templates</h1>
    <p class="text-sm text-gray-500 mt-0.5">Reusable templates for email, WhatsApp, and SMS communications</p>
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
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Locale</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($templates as $t)
                        @php
                            $channelColors = ['email' => 'blue', 'whatsapp' => 'emerald', 'sms' => 'violet'];
                            $cc = $channelColors[$t->channel] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-800">{{ $t->name }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-medium bg-{{ $cc }}-50 text-{{ $cc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $t->channel }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">{{ $t->locale }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if ($t->is_active)
                                <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                                @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-sm text-gray-400">No templates yet.</td>
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
        <form method="POST" action="{{ route('panel.comm.templates.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Booking Confirmation ID"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Channel <span class="text-red-500">*</span></label>
                <select name="channel" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="email">Email</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="sms">SMS</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Subject (email)</label>
                <input type="text" name="subject" placeholder="Your booking is confirmed"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Body <span class="text-red-500">*</span></label>
                <textarea name="body" required rows="5" placeholder="Use &#123;&#123;guest_name&#125;&#125;, &#123;&#123;ref&#125;&#125;…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Locale</label>
                <input type="text" name="locale" value="id" placeholder="id / en"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Create Template
            </button>
        </form>
    </div>

</div>

@endsection
