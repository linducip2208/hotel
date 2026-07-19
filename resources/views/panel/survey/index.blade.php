@extends('panel.layout')
@section('title', 'Surveys')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Surveys</h1>
    <p class="text-sm text-gray-500 mt-0.5">Guest satisfaction surveys and NPS tracking</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Survey list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Active Surveys</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($surveys as $s)
                @php
                    $triggerColors = ['post_stay' => 'blue', 'post_event' => 'violet', 'in_stay' => 'emerald', 'on_demand' => 'amber'];
                    $tc = $triggerColors[$s->trigger] ?? 'gray';
                @endphp
                <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50/60 transition-colors">
                    <div class="w-9 h-9 rounded-xl bg-{{ $tc }}-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-{{ $tc }}-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900">{{ $s->name }}</div>
                        <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                            <span class="font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2 py-0.5 rounded-full capitalize">{{ str_replace('_', '-', $s->trigger) }}</span>
                            <span>{{ count($s->questions ?? []) }} questions</span>
                            <span>{{ $s->responses_count ?? 0 }} responses</span>
                        </div>
                    </div>
                    <a href="{{ route('panel.survey.responses', $s->id) }}"
                       class="text-xs font-medium text-primary-600 bg-primary-50 px-3 py-1.5 rounded-lg hover:bg-primary-100 transition-colors shrink-0">
                        Responses
                    </a>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <p class="text-sm text-gray-500">No surveys yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- New survey form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit"
         x-data='{ qs: [{key:"overall", type:"rating", prompt:"Overall experience"}, {key:"comments", type:"text", prompt:"Comments"}, {key:"nps", type:"nps", prompt:"How likely to recommend?"}] }'>
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">New Survey</h2>
        </div>
        <form method="POST" action="{{ route('panel.survey.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Survey Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Post-stay satisfaction"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Trigger <span class="text-red-500">*</span></label>
                <select name="trigger" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="post_stay">Post-stay</option>
                    <option value="post_event">Post-event</option>
                    <option value="in_stay">In-stay</option>
                    <option value="on_demand">On-demand</option>
                </select>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold text-gray-600">Questions</label>
                    <button type="button" @click="qs.push({key:'',type:'text',prompt:''})"
                            class="text-xs text-primary-600 hover:text-primary-800 font-medium">+ Add</button>
                </div>
                <div class="space-y-2">
                    <template x-for="(q, i) in qs" :key="i">
                        <div class="flex gap-2 items-start">
                            <div class="flex-1 space-y-1.5">
                                <input :name="'questions['+i+'][key]'" x-model="q.key" placeholder="key"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs outline-none focus:border-primary-400 transition-all">
                                <div class="flex gap-1.5">
                                    <select :name="'questions['+i+'][type]'" x-model="q.type"
                                            class="flex-1 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs outline-none focus:border-primary-400 transition-all">
                                        <option value="rating">Rating</option>
                                        <option value="text">Text</option>
                                        <option value="nps">NPS</option>
                                        <option value="multi">Multi</option>
                                    </select>
                                    <input :name="'questions['+i+'][prompt]'" x-model="q.prompt" placeholder="Prompt"
                                           class="flex-1 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs outline-none focus:border-primary-400 transition-all">
                                </div>
                            </div>
                            <button type="button" @click="qs.splice(i, 1)"
                                    class="text-gray-300 hover:text-red-400 transition-colors mt-1 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Create Survey
            </button>
        </form>
    </div>

</div>

@endsection
