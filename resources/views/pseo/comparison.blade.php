@empty($competition) @php return; @endphp @endempty

@php
    $comp = $competition;
    $cols = $comp['competitors'];
    $cats = $comp['categories'];
    $summary = $comp['summary'];
    $totalFeatures = 0;
    foreach ($cats as $cat) { $totalFeatures += count($cat['features']); }
@endphp

<section id="perbandingan-kompetitor" class="reveal mb-12 scroll-mt-28">
    <div class="flex items-center gap-2.5 mb-2">
        <span class="text-2xl">⚔️</span>
        <h2 class="font-display text-2xl md:text-3xl font-bold text-slate-900">Perbandingan Kompetitor</h2>
    </div>
    <p class="text-slate-500 text-sm mb-3 max-w-2xl">
        <strong>{{ $totalFeatures }} fitur</strong> dibandingkan over <strong>{{ count($cats) }} kategori</strong> terhadap 4 kompetitor: QloApps, HotelDruid, FewohBee, dan ERPNext Hospitality.
    </p>

    <p class="text-xs text-slate-400 mb-5 leading-relaxed">
        <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Tersedia</span>
        <span class="inline-flex items-center gap-1 ml-3"><svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> Tidak tersedia</span>
    </p>

    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto" x-data="{ stickyTop: 0 }" x-init="stickyTop = $el.getBoundingClientRect().top">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80">
                        <th class="sticky left-0 z-10 bg-slate-50/80 backdrop-blur text-left py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-[0.1em] w-48">Fitur</th>
                        @foreach ($cols as $ci => $col)
                            <th class="py-3.5 px-3 text-center text-xs font-bold uppercase tracking-[0.08em] border-l border-slate-200 {{ $col['highlight'] ?? false ? 'bg-indigo-600 text-white' : 'text-slate-500' }}">
                                <div>{{ $col['name'] }}</div>
                                <div class="text-[9px] mt-0.5 font-normal {{ $col['highlight'] ?? false ? 'text-indigo-200' : 'text-slate-400' }}">{{ $col['flag'] }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cats as $cat)
                        <tr class="bg-slate-100/80">
                            <td colspan="{{ count($cols) + 1 }}" class="py-2.5 px-4 text-xs font-bold text-slate-600 uppercase tracking-[0.08em]">
                                {{ $cat['category'] }}
                            </td>
                        </tr>
                        @foreach ($cat['features'] as $feat)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/60 transition-colors">
                                <td class="sticky left-0 z-[5] bg-white py-3 px-4 text-slate-700 font-medium text-xs whitespace-nowrap">
                                    {{ $feat['label'] }}
                                </td>
                                @foreach (['hotelhms', 'qlo', 'hdruid', 'fewoh', 'erp'] as $ki)
                                    @php $isOur = ($ki === 'hotelhms'); @endphp
                                    <td class="py-3 px-3 text-center border-l border-slate-100 {{ $isOur ? 'bg-indigo-50/60' : '' }}">
                                        @if ($feat[$ki])
                                            <svg class="w-4 h-4 text-emerald-500 mx-auto" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 text-rose-400 mx-auto" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-indigo-50 border-t-2 border-indigo-200">
                        <td class="sticky left-0 z-[5] bg-indigo-50 py-3.5 px-4 text-xs font-bold text-slate-700 uppercase tracking-[0.08em]">
                            Total Score
                        </td>
                        <td class="py-3.5 px-3 text-center bg-indigo-600">
                            <span class="inline-flex items-center gap-1 text-white font-bold text-sm">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $summary['hotelhms_score'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3 text-center border-l border-indigo-200">
                            <span class="text-slate-500 font-semibold text-xs">{{ $summary['qlo_score'] }}</span>
                        </td>
                        <td class="py-3.5 px-3 text-center border-l border-indigo-200">
                            <span class="text-slate-500 font-semibold text-xs">{{ $summary['hdruid_score'] }}</span>
                        </td>
                        <td class="py-3.5 px-3 text-center border-l border-indigo-200">
                            <span class="text-slate-500 font-semibold text-xs">{{ $summary['fewoh_score'] }}</span>
                        </td>
                        <td class="py-3.5 px-3 text-center border-l border-indigo-200">
                            <span class="text-slate-500 font-semibold text-xs">{{ $summary['erp_score'] }}</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Competitor summary cards --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-5">
        <div class="bg-white border border-slate-200 rounded-xl p-4 card-lift">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <span class="font-semibold text-xs text-slate-800">QloApps</span>
                <span class="text-[10px] text-slate-400">PHP Open Source</span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">PMS dasar + booking engine. Tidak ada channel manager, accounting, Indonesia compliance, atau AI tools. Cocok untuk guesthouse kecil &le;50 kamar.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 card-lift">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span class="font-semibold text-xs text-slate-800">HotelDruid</span>
                <span class="text-[10px] text-slate-400">Italian Open Source</span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">FO kuat + modul restoran. Tapi UI desktop-oriented, tidak ada channel manager, tidak ada Indonesia tax/compliance.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 card-lift">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="font-semibold text-xs text-slate-800">FewohBee</span>
                <span class="text-[10px] text-slate-400">German SaaS</span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">Fokus vacation rental. Channel manager dasar tapi tidak ada full PMS, F&B/POS, accounting, atau Indonesia compliance.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 card-lift">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span class="font-semibold text-xs text-slate-800">ERPNext Hospitality</span>
                <span class="text-[10px] text-slate-400">ERPNext Module</span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">Accounting kuat (ERPNext core). Tapi setup berat, tidak ada native OTA integrations, booking engine, atau Indonesia tax.</p>
        </div>
    </div>
</section>
