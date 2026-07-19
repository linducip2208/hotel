@extends('panel.layout')
@section('title', 'Create Campaign')
@section('content')

<div class="max-w-3xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Campaign</h1>
        <p class="text-sm text-gray-500 mt-0.5">Build a new email or WhatsApp broadcast campaign</p>
    </div>

    <form method="POST" action="{{ route('panel.comm.campaigns.store') }}" class="space-y-6" x-data="campaignBuilder()">
        @csrf

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Campaign Details</h2>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Campaign Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Ramadan Special Offer 2025"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Channel <span class="text-red-500">*</span></label>
                        <select name="channel" required x-model="channel"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="both">Email + WhatsApp</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Template</label>
                        <select name="template_id"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                            <option value="">No template</option>
                            @foreach ($templates as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->channel }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div x-show="channel === 'email' || channel === 'both'">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Subject Line</label>
                    <input type="text" name="subject" placeholder="Your subject line"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Message Body</label>
                    <textarea name="body" rows="5" placeholder="Write your message. Use {guest_name} for personalization."
                              class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all"></textarea>
                    <p class="text-xs text-gray-400 mt-1">Available variables: {guest_name}, {first_name}, {last_name}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Target Audience</h2>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Audience <span class="text-red-500">*</span></label>
                    <select name="target_audience" required x-model="audience"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="all_guests">All Guests</option>
                        <option value="vip_only">VIP Only</option>
                        <option value="by_last_stay">Stayed in Last 6 Months</option>
                        <option value="by_birthday_month">Birthday This Month</option>
                        <option value="custom_filter">Custom Filter</option>
                    </select>
                </div>
                <div x-show="audience === 'custom_filter'" class="bg-gray-50 rounded-xl p-4 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Country</label>
                        <input type="text" name="custom_filter[country]" placeholder="e.g. ID, SG, MY"
                               class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Minimum Stays</label>
                        <input type="number" name="custom_filter[min_stays]" min="1" placeholder="e.g. 3"
                               class="w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Schedule</h2>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">When to Send <span class="text-red-500">*</span></label>
                    <select name="schedule_type" required x-model="schedule"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="send_now">Send Immediately</option>
                        <option value="schedule_date">Schedule for Later</option>
                        <option value="recurring">Recurring</option>
                    </select>
                </div>
                <div x-show="schedule === 'schedule_date'">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date & Time</label>
                    <input type="datetime-local" name="scheduled_at"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div x-show="schedule === 'recurring'">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Cron Expression</label>
                    <input type="text" name="recurring_cron" placeholder="0 9 * * 1 (every Monday 9am)"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <p class="text-xs text-gray-400 mt-1">Standard cron format: minute hour day month weekday</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-sm transition-colors">
                Create & {{ request('schedule_type') === 'schedule_date' ? 'Schedule' : 'Send' }}
            </button>
            <a href="{{ route('panel.comm.campaigns') }}"
               class="bg-white border border-gray-200 hover:bg-gray-50 text-sm font-medium px-6 py-3 rounded-xl transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
function campaignBuilder() {
    return {
        channel: 'email',
        audience: 'all_guests',
        schedule: 'send_now',
    };
}
</script>

@endsection
