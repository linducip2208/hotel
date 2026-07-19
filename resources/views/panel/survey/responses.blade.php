@extends('panel.layout')
@section('title', 'Survey Responses')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.survey.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $survey->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">Survey responses and NPS analysis</p>
    </div>
</div>

{{-- NPS KPI cards --}}
<div class="grid grid-cols-3 gap-5 mb-5">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">NPS Score</div>
        <div class="text-4xl font-bold {{ $nps >= 50 ? 'text-emerald-600' : ($nps >= 0 ? 'text-amber-600' : 'text-red-600') }}">
            {{ $nps }}
        </div>
        <div class="text-xs text-gray-400 mt-1">
            {{ $nps >= 50 ? 'Excellent' : ($nps >= 30 ? 'Good' : ($nps >= 0 ? 'Needs Work' : 'Poor')) }}
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Promoters</div>
        <div class="text-4xl font-bold text-emerald-600">{{ $promoters }}</div>
        <div class="text-xs text-gray-400 mt-1">Score 9–10</div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Detractors</div>
        <div class="text-4xl font-bold text-red-500">{{ $detractors }}</div>
        <div class="text-xs text-gray-400 mt-1">Score 0–6</div>
    </div>
</div>

{{-- Responses table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Guest</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">NPS</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Sentiment</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Answers</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($survey->responses as $r)
                @php
                    $npsScore = $r->nps_score;
                    $npsColor = $npsScore >= 9 ? 'emerald' : ($npsScore <= 6 ? 'red' : 'amber');
                    $sentimentColor = match($r->sentiment) { 'positive' => 'emerald', 'negative' => 'red', 'neutral' => 'gray', default => 'gray' };
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-xs text-gray-500 font-mono">{{ $r->submitted_at->format('d M H:i') }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $r->guest?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($npsScore !== null)
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold bg-{{ $npsColor }}-50 text-{{ $npsColor }}-700">
                            {{ $npsScore }}
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if ($r->sentiment)
                        <span class="text-xs font-medium bg-{{ $sentimentColor }}-50 text-{{ $sentimentColor }}-700 px-2 py-0.5 rounded-full capitalize">{{ $r->sentiment }}</span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <code class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md font-mono block max-w-xs truncate">{{ json_encode($r->answers) }}</code>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center text-sm text-gray-400">No responses yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
