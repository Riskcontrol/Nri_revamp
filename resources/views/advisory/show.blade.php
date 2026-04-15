<x-layout title="Travel Advisory — {{ $state }} State"
    description="NRI Travel Advisory for {{ $state }} — current security conditions and travel guidance based on rolling 12-month data.">

    <style>
        .r1 {
            --rc: rgba(16, 185, 129, .18);
            --rb: rgba(16, 185, 129, .4);
            --rt: #34d399
        }

        .r2 {
            --rc: rgba(234, 179, 8, .18);
            --rb: rgba(234, 179, 8, .4);
            --rt: #fbbf24
        }

        .r3 {
            --rc: rgba(249, 115, 22, .18);
            --rb: rgba(249, 115, 22, .4);
            --rt: #fb923c
        }

        .r4 {
            --rc: rgba(239, 68, 68, .2);
            --rb: rgba(239, 68, 68, .45);
            --rt: #f87171
        }

        .risk-badge {
            background: var(--rc);
            border: 1px solid var(--rb);
            color: var(--rt)
        }

        .risk-banner {
            background: var(--rc);
            border-left: 3px solid var(--rb)
        }

        .sc {
            background: #ef4444
        }

        .sh {
            background: #f97316
        }

        .sm {
            background: #eab308
        }

        .sl {
            background: #22c55e
        }

        .tl {
            background: rgba(16, 185, 129, .06)
        }

        .tm {
            background: rgba(234, 179, 8, .06)
        }

        .th {
            background: rgba(249, 115, 22, .07)
        }

        .tvh {
            background: rgba(239, 68, 68, .09)
        }

        @keyframes shimmer {
            0% {
                background-position: -600px 0
            }

            100% {
                background-position: 600px 0
            }
        }

        .sk {
            background: linear-gradient(90deg, #1a2535 25%, #243040 50%, #1a2535 75%);
            background-size: 600px 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 5px
        }

        @keyframes fu {
            from {
                opacity: 0;
                transform: translateY(8px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .fu {
            animation: fu .3s ease both
        }

        .tu {
            color: #f87171;
            font-weight: 600
        }

        .td {
            color: #34d399;
            font-weight: 600
        }

        .ts {
            color: #6b7280
        }

        .sig-card {
            background: #111c2b;
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 10px;
            padding: 14px 16px
        }

        .sp {
            color: #c4cdd8;
            line-height: 1.72;
            font-size: .875rem
        }

        .sp+.sp {
            margin-top: .9rem
        }

        .sp:first-child {
            color: #dde4ed
        }

        .score-bar {
            height: 4px;
            border-radius: 2px;
            background: rgba(255, 255, 255, .08)
        }

        .score-fill {
            height: 100%;
            border-radius: 2px;
            transition: width .6s ease;
            background: var(--rb)
        }
    </style>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">

        {{-- ═══ PAGE HEADER ══════════════════════════════════════════════════════ --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold tracking-[0.15em] text-emerald-400 uppercase mb-1">
                    Travel Advisory
                </p>
                <h1 class="text-3xl font-bold text-white tracking-tight">{{ $state }} State</h1>
                <p id="window-label" class="text-xs text-gray-500 mt-1.5 hidden">
                    <span class="text-gray-600">Data period:</span>
                    <span id="window-label-text" class="text-gray-400"></span>
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <div class="relative">
                    <select id="state-select"
                        class="appearance-none bg-[#111c2b] text-white text-sm py-2 pl-4 pr-9
                               border border-white/10 rounded-lg focus:outline-none focus:border-emerald-500
                               hover:border-white/20 transition-colors cursor-pointer">
                        @foreach ($allStates as $s)
                            <option value="{{ $s }}" {{ $s === $state ? 'selected' : '' }}>
                                {{ $s }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500 pointer-events-none"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <span id="last-updated-wrap" class="hidden text-[11px] text-gray-600 whitespace-nowrap">
                    Updated <span id="last-updated-text" class="text-gray-500"></span>
                </span>
            </div>
        </div>

        {{-- ═══ ADVISORY BANNER ══════════════════════════════════════════════════ --}}
        <div id="advisory-banner" class="risk-banner r4 rounded-xl p-5 fu">
            <div class="flex items-start gap-4">

                <div class="flex-shrink-0 w-11 h-11 rounded-xl flex items-center justify-center mt-0.5"
                    style="background:var(--rc);border:1px solid var(--rb)">
                    <svg class="w-5 h-5" style="color:var(--rt)" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.32 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.68-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z"
                            clip-rule="evenodd" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span id="advisory-badge"
                            class="risk-badge r4 text-[11px] font-bold tracking-widest px-3 py-1 rounded-full uppercase">
                            <span id="advisory-badge-text">Loading…</span>
                        </span>
                    </div>

                    <p id="advisory-label" class="text-white font-bold text-xl leading-tight mb-3">
                        <span class="sk inline-block h-6 w-64 rounded"></span>
                    </p>

                    {{-- Score bar --}}
                    <div id="score-section" class="hidden">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] text-gray-500 uppercase tracking-wider">Risk Score</span>
                            <span class="text-xs font-semibold" style="color:var(--rt)">
                                <span id="score-val"></span><span class="text-gray-600">/100</span>
                            </span>
                        </div>
                        <div class="score-bar">
                            <div id="score-fill" class="score-fill" style="width:0%"></div>
                        </div>
                    </div>

                    <p class="text-gray-500 text-[11px] mt-3">
                        Rolling 12-month window · Current conditions
                    </p>
                </div>
            </div>
        </div>

        {{-- ═══ SITUATION + SIGNALS ═══════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

            {{-- Current Situation (2/5) --}}
            <div class="lg:col-span-2 bg-[#0e1824] rounded-xl p-5 border border-white/5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full bg-emerald-500 opacity-70"></div>
                    <h2 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Current Situation</h2>
                </div>
                <div id="situation-container">
                    <span class="sk block h-3.5 w-full mb-2 rounded"></span>
                    <span class="sk block h-3.5 w-5/6 mb-2 rounded"></span>
                    <span class="sk block h-3.5 w-full mb-5 rounded"></span>
                    <span class="sk block h-3.5 w-full mb-2 rounded"></span>
                    <span class="sk block h-3.5 w-4/5 mb-5 rounded"></span>
                    <span class="sk block h-3.5 w-5/6 mb-2 rounded"></span>
                    <span class="sk block h-3.5 w-3/4 rounded"></span>
                </div>
            </div>

            {{-- Key Risk Signals (3/5) --}}
            <div class="lg:col-span-3 bg-[#0e1824] rounded-xl p-5 border border-white/5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full bg-red-500 opacity-70"></div>
                    <h2 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Key Risk Signals</h2>
                </div>
                <div id="risk-signals-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="sk rounded-xl h-[88px]"></div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- ═══ OPERATIONAL GUIDANCE ══════════════════════════════════════════════ --}}
        <div class="bg-[#0e1824] rounded-xl border border-yellow-500/15 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-4 border-b border-white/5">
                <div
                    class="w-7 h-7 rounded-lg bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-yellow-300">Operational Guidance</h2>
                    <p class="text-[11px] text-gray-600">Actions to take before and during travel</p>
                </div>
            </div>
            <div class="p-5">
                <ul id="operational-guidance" class="space-y-3">
                    @for ($i = 0; $i < 5; $i++)
                        <li class="sk h-4 rounded w-full"></li>
                    @endfor
                </ul>
            </div>
        </div>

        {{-- ═══ LGA RISK TABLE ════════════════════════════════════════════════════ --}}
        <div class="bg-[#0e1824] rounded-xl border border-white/5 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                <div>
                    <h2 class="text-sm font-bold text-white">LGA Risk Breakdown</h2>
                    <p id="table-subtitle" class="text-[11px] text-gray-600 mt-0.5">
                        Most incident-active areas · rolling 12-month window
                    </p>
                </div>
                <span id="live-badge"
                    class="hidden text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20
                             px-2.5 py-1 rounded-full tracking-widest uppercase">
                    Live Data
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5 bg-[#0a1420]">
                            <th
                                class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest">
                                Area / LGA</th>
                            <th
                                class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest">
                                Risk Level</th>
                            <th
                                class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest hidden sm:table-cell">
                                Incidents</th>
                            <th
                                class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest hidden md:table-cell">
                                Trend</th>
                            <th
                                class="px-5 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-widest hidden lg:table-cell">
                                Primary Threat &amp; Advisory</th>
                        </tr>
                    </thead>
                    <tbody id="risk-table-body">
                        @for ($i = 0; $i < 3; $i++)
                            <tr class="border-b border-white/5">
                                <td class="px-5 py-4"><span class="sk block h-4 w-32 rounded"></span></td>
                                <td class="px-5 py-4"><span class="sk block h-6 w-20 rounded-full"></span></td>
                                <td class="px-5 py-4 hidden sm:table-cell"><span
                                        class="sk block h-4 w-10 rounded"></span></td>
                                <td class="px-5 py-4 hidden md:table-cell"><span
                                        class="sk block h-4 w-24 rounded"></span></td>
                                <td class="px-5 py-4 hidden lg:table-cell"><span
                                        class="sk block h-4 w-52 rounded"></span></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-white/5 bg-[#0a1420]">
                <p class="text-[10px] text-gray-700">
                    Risk levels are relative to other areas within this state. For national comparison, see the Risk
                    Map.
                </p>
            </div>
        </div>

        <p class="text-center text-[11px] text-gray-700 pb-2">
            NRI supports safer decisions through data-driven risk intelligence.
        </p>

    </div>

    <script>
        (function() {
            'use strict';

            const STATE = @json($state);
            const DATA_URL = s => `/advisory/${encodeURIComponent(s)}/data`;

            const ICONS = {
                shield: `<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.32 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.68-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/></svg>`,
                alert: `<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>`,
                eye: `<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>`,
                map: `<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>`,
            };

            // Risk level → display config
            // Uses CSS classes r1..r4 defined above
            const RISK = {
                1: {
                    cls: 'r1',
                    text: 'LEVEL 1 – LOW RISK',
                    badge: 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                },
                2: {
                    cls: 'r2',
                    text: 'LEVEL 2 – MODERATE RISK',
                    badge: 'bg-yellow-500/20  text-yellow-300  border border-yellow-500/30'
                },
                3: {
                    cls: 'r3',
                    text: 'LEVEL 3 – HIGH RISK',
                    badge: 'bg-orange-500/20  text-orange-300  border border-orange-500/30'
                },
                4: {
                    cls: 'r4',
                    text: 'LEVEL 4 – VERY HIGH RISK',
                    badge: 'bg-red-500/20     text-red-300     border border-red-500/30'
                },
            };

            const TABLE_BG = {
                'Low': 'tl',
                'Medium-High': 'tm',
                'High': 'th',
                'Very High': 'tvh'
            };
            const SEV_CLS = {
                critical: 'sc',
                high: 'sh',
                moderate: 'sm',
                low: 'sl'
            };

            document.getElementById('state-select').addEventListener('change', function() {
                window.location.href = `/advisory/${encodeURIComponent(this.value)}`;
            });

            // ── Fetch ─────────────────────────────────────────────────────────────
            async function loadAdvisory(state) {
                try {
                    const res = await fetch(DATA_URL(state), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    render(await res.json());
                } catch (err) {
                    console.error('Advisory fetch failed:', err);
                    document.getElementById('situation-container').innerHTML =
                        `<p class="text-sm text-gray-500 text-center py-6">
                        Advisory data could not be loaded.
                        <button onclick="loadAdvisory('${esc(STATE)}')"
                                class="text-emerald-400 hover:underline ml-1">Retry</button>
                    </p>`;
                }
            }

            // ── Render ────────────────────────────────────────────────────────────
            function render(data) {
                const adv = data.advisory ?? {};

                // ── Risk level ─────────────────────────────────────────────────────
                // ALWAYS use data.risk_level (set from payload in the controller).
                // Never use adv.advisory_level — the AI's output is unreliable and
                // is now overridden by the aggregator's computed value at the DB layer.
                const level = parseInt(data.risk_level, 10) || 1;
                const score = parseFloat(data.risk_score) || 0;
                const risk = RISK[level] ?? RISK[1];

                // Banner colours
                const banner = document.getElementById('advisory-banner');
                banner.className = banner.className.replace(/\br\d\b/, risk.cls) + ' fu';

                // Badge
                const badge = document.getElementById('advisory-badge');
                badge.className = badge.className.replace(/\br\d\b/, risk.cls);
                document.getElementById('advisory-badge-text').textContent =
                    `TRAVEL ADVISORY: ${risk.text}`;

                // Label (from AI narrative — display text only, not level logic)
                const labelEl = document.getElementById('advisory-label');
                labelEl.innerHTML = '';
                labelEl.textContent = adv.advisory_label ?? '';

                // Score bar
                if (score > 0) {
                    document.getElementById('score-section').classList.remove('hidden');
                    document.getElementById('score-val').textContent = score;
                    setTimeout(() => {
                        document.getElementById('score-fill').style.width = `${Math.min(score, 100)}%`;
                    }, 120);
                }

                // Window label
                if (data.window_label) {
                    document.getElementById('window-label-text').textContent = data.window_label;
                    document.getElementById('window-label').classList.remove('hidden');
                }

                // Last updated
                if (data.last_updated) {
                    document.getElementById('last-updated-text').textContent =
                        new Date(data.last_updated).toLocaleDateString('en-GB', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        });
                    document.getElementById('last-updated-wrap').classList.remove('hidden');
                }

                renderSituation(adv.current_situation ?? '');
                renderSignals(adv.key_risk_signals ?? []);
                renderGuidance(adv.operational_guidance ?? []);
                renderTable(data.risk_table ?? []);
            }

            // ── Situation ─────────────────────────────────────────────────────────
            function renderSituation(text) {
                const container = document.getElementById('situation-container');
                const paras = text.split(/\n\n+/).filter(p => p.trim());
                if (!paras.length) {
                    container.innerHTML = `<p class="sp">${esc(text)}</p>`;
                    return;
                }
                container.innerHTML = paras.map((p, i) =>
                    `<p class="sp fu" style="animation-delay:${i * 80}ms">${esc(p.trim())}</p>`
                ).join('');
            }

            // ── Signals ───────────────────────────────────────────────────────────
            function renderSignals(signals) {
                document.getElementById('risk-signals-grid').innerHTML = signals.map((s, i) =>
                    `<div class="sig-card fu" style="animation-delay:${i * 65}ms">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center
                                    bg-white/5 text-gray-400 mt-0.5">
                            ${ICONS[s.icon ?? 'alert'] ?? ICONS.alert}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-2 h-2 rounded-full flex-shrink-0 ${SEV_CLS[s.severity] ?? 'sm'}"></span>
                                <span class="text-xs font-bold text-white leading-tight">${esc(s.signal)}</span>
                            </div>
                            <p class="text-[11px] text-gray-500 leading-relaxed">${esc(s.detail)}</p>
                        </div>
                    </div>
                </div>`
                ).join('');
            }

            // ── Guidance ──────────────────────────────────────────────────────────
            function renderGuidance(items) {
                document.getElementById('operational-guidance').innerHTML = items.map((g, i) =>
                    `<li class="flex items-start gap-3 fu" style="animation-delay:${i * 50}ms">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-yellow-500/10 border border-yellow-500/20
                                 flex items-center justify-center mt-0.5">
                        <svg class="w-2.5 h-2.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <span class="text-sm text-gray-300 leading-relaxed">${esc(g)}</span>
                </li>`
                ).join('');
            }

            // ── LGA Table ─────────────────────────────────────────────────────────
            function renderTable(rows) {
                const body = document.getElementById('risk-table-body');

                if (!rows || !rows.length) {
                    body.innerHTML = `<tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-600">
                    Area data unavailable for this state.</td></tr>`;
                    return;
                }

                const hasLga = rows.some(r => r.incident_count !== null);
                if (hasLga) {
                    document.getElementById('live-badge').classList.remove('hidden');
                    document.getElementById('table-subtitle').textContent =
                        'Ranked by incident volume · rolling 12-month window';
                }

                body.innerHTML = rows.map((row, i) => {
                    const bg = TABLE_BG[row.risk_label] ?? '';
                    const badgeCls = RISK[row.risk_level]?.badge ?? '';
                    const delay = i * 75;

                    const incCell = row.incident_count !== null ?
                        `<span class="font-bold text-white text-base">${row.incident_count}</span>
                       <span class="text-gray-600 text-[11px] ml-1">incidents</span>` :
                        `<span class="text-gray-700 text-xs">—</span>`;

                    let trendCell = `<span class="text-gray-700 text-xs">—</span>`;
                    if (row.trend) {
                        const tc = row.trend === 'rising' ? 'tu' : (row.trend === 'falling' ? 'td' : 'ts');
                        const yoyText = row.yoy_change !== null ?
                            ` <span class="font-normal text-gray-600">(${row.yoy_change > 0 ? '+' : ''}${row.yoy_change}%)</span>` :
                            '';
                        trendCell =
                            `<span class="${tc} text-xs">${row.trend_symbol} ${row.trend_label}${yoyText}</span>`;
                    }

                    let advCell = esc(row.advisory ?? '—');
                    if (row.dominant_type) {
                        advCell = `<span class="inline-block text-[10px] font-semibold bg-white/5 border border-white/10
                                      text-gray-400 px-2 py-0.5 rounded mb-1.5">${esc(row.dominant_type)}</span>
                               <br><span class="text-[11px] text-gray-500">${esc(row.advisory ?? '')}</span>`;
                    }

                    return `<tr class="border-b border-white/5 ${bg} fu" style="animation-delay:${delay}ms">
                    <td class="px-5 py-3.5">
                        <span class="text-sm font-semibold text-gray-200">${esc(row.area_type)}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold ${badgeCls}">
                            ${esc(row.risk_label)}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 hidden sm:table-cell">${incCell}</td>
                    <td class="px-5 py-3.5 hidden md:table-cell">${trendCell}</td>
                    <td class="px-5 py-3.5 hidden lg:table-cell max-w-xs">${advCell}</td>
                </tr>`;
                }).join('');
            }

            function esc(str) {
                const d = document.createElement('div');
                d.appendChild(document.createTextNode(str ?? ''));
                return d.innerHTML;
            }

            loadAdvisory(STATE);
        })();
    </script>

</x-layout>
