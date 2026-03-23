<x-layout title="{{ $incident->caption ?? 'Security Alert' }} — NRI">

    <div class="min-h-screen bg-[#080E1A]">

        {{-- ── Alert hero ──────────────────────────────────────────────────────── --}}
        @php
            $impactColors = [
                'Critical' => ['bg' => '#991B1B', 'badge' => '#B91C1C', 'text' => '#FCA5A5'],
                'High' => ['bg' => '#92400E', 'badge' => '#C2410C', 'text' => '#FCA5A5'],
                'Medium' => ['bg' => '#78350F', 'badge' => '#B45309', 'text' => '#FCD34D'],
                'Low' => ['bg' => '#064E3B', 'badge' => '#047857', 'text' => '#6EE7B7'],
            ];
            $clr = $impactColors[$incident->impact_label] ?? $impactColors['High'];
        @endphp

        <div
            style="background:linear-gradient(135deg,{{ $clr['bg'] }} 0%,#0D1627 60%);
                border-bottom:1px solid rgba(255,255,255,0.08);padding:48px 24px 40px">
            <div style="max-width:860px;margin:0 auto">

                {{-- Breadcrumb --}}
                <div
                    style="display:flex;align-items:center;gap:8px;margin-bottom:24px;font-size:12px;color:rgba(255,255,255,0.45)">
                    <a href="{{ route('news') }}"
                        style="color:rgba(255,255,255,0.55);text-decoration:none;
                   transition:color 0.15s"
                        onmouseover="this.style.color='#fff'"
                        onmouseout="this.style.color='rgba(255,255,255,0.55)'">Security Hub</a>
                    <span>/</span>
                    <span style="color:rgba(255,255,255,0.35)">Active Alert</span>
                </div>

                {{-- Impact badge --}}
                <div
                    style="display:inline-flex;align-items:center;gap:8px;padding:4px 12px;border-radius:6px;
                        background:{{ $clr['badge'] }};margin-bottom:16px">
                    <span
                        style="width:7px;height:7px;border-radius:50%;background:#fff;
                             animation:pulse-dot 1.5s ease-in-out infinite;display:inline-block"></span>
                    <span
                        style="font-size:10px;font-weight:900;letter-spacing:0.14em;color:#fff;text-transform:uppercase">
                        {{ $incident->impact_label }} ALERT
                    </span>
                </div>

                {{-- Headline --}}
                <h1
                    style="font-size:clamp(22px,4vw,34px);font-weight:800;color:#fff;
                       line-height:1.2;margin:0 0 20px;letter-spacing:-0.01em">
                    {{ $incident->caption }}
                </h1>

                {{-- Meta row --}}
                <div style="display:flex;flex-wrap:wrap;gap:20px;font-size:12px;color:rgba(255,255,255,0.6)">
                    @if ($incident->location)
                        <span style="display:flex;align-items:center;gap:5px">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            {{ $incident->location }}{{ $incident->lga ? ', ' . $incident->lga : '' }}
                        </span>
                    @endif
                    @if ($incident->formatted_date)
                        <span style="display:flex;align-items:center;gap:5px">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            {{ $incident->formatted_date }}
                        </span>
                    @endif
                    @if ($incident->riskfactors)
                        <span style="display:flex;align-items:center;gap:5px">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            </svg>
                            {{ $incident->riskfactors }}
                        </span>
                    @endif
                    @if ($incident->Casualties_count)
                        <span style="display:flex;align-items:center;gap:5px;color:#FCA5A5">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            {{ $incident->Casualties_count }} Casualties
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Alert body ──────────────────────────────────────────────────────── --}}
        <div style="max-width:860px;margin:0 auto;padding:40px 24px 80px">

            <div style="display:grid;grid-template-columns:1fr min(280px,35%);gap:24px;align-items:start"
                class="alert-grid">

                {{-- LEFT — main content --}}
                <div>

                    {{-- Summary --}}
                    @if ($incident->add_notes)
                        <div
                            style="background:#0D1627;border:1px solid rgba(255,255,255,0.08);
                            border-radius:12px;padding:24px;margin-bottom:20px">
                            <p
                                style="font-size:10px;font-weight:900;letter-spacing:0.16em;
                              color:rgba(255,255,255,0.35);text-transform:uppercase;margin:0 0 12px">
                                Summary
                            </p>
                            <p style="font-size:15px;line-height:1.75;color:rgba(255,255,255,0.85);margin:0">
                                {{ $incident->add_notes }}
                            </p>
                        </div>
                    @endif

                    {{-- Weekly narrative / full detail --}}
                    @if ($incident->weekly_summary)
                        <div
                            style="background:#0D1627;border:1px solid rgba(255,255,255,0.08);
                            border-radius:12px;padding:24px;margin-bottom:20px">
                            <p
                                style="font-size:10px;font-weight:900;letter-spacing:0.16em;
                              color:rgba(255,255,255,0.35);text-transform:uppercase;margin:0 0 12px">
                                Detailed Intelligence
                            </p>
                            <p style="font-size:14px;line-height:1.8;color:rgba(255,255,255,0.75);margin:0">
                                {{ $incident->weekly_summary }}
                            </p>
                        </div>
                    @endif

                    {{-- Risk assessment --}}
                    @if ($incident->associated_risks)
                        <div
                            style="background:rgba(185,28,28,0.08);border:1px solid rgba(185,28,28,0.25);
                            border-radius:12px;padding:24px;margin-bottom:20px">
                            <p
                                style="font-size:10px;font-weight:900;letter-spacing:0.16em;
                              color:rgba(252,165,165,0.7);text-transform:uppercase;margin:0 0 12px">
                                Risk Assessment
                            </p>
                            <p style="font-size:14px;line-height:1.75;color:rgba(255,255,255,0.8);margin:0">
                                {{ $incident->associated_risks }}
                            </p>
                        </div>
                    @endif

                    {{-- Business advisory --}}
                    @if ($incident->business_advisory)
                        <div
                            style="background:rgba(180,83,9,0.08);border:1px solid rgba(180,83,9,0.25);
                            border-radius:12px;padding:24px;margin-bottom:20px">
                            <p
                                style="font-size:10px;font-weight:900;letter-spacing:0.16em;
                              color:rgba(253,211,77,0.7);text-transform:uppercase;margin:0 0 12px">
                                Business Advisory
                            </p>
                            <p style="font-size:14px;line-height:1.75;color:rgba(255,255,255,0.8);margin:0">
                                {{ $incident->business_advisory }}
                            </p>
                        </div>
                    @endif

                    {{-- Impact rationale --}}
                    @if ($incident->impact_rationale)
                        <div
                            style="background:#0D1627;border:1px solid rgba(255,255,255,0.08);
                            border-radius:12px;padding:24px;margin-bottom:20px">
                            <p
                                style="font-size:10px;font-weight:900;letter-spacing:0.16em;
                              color:rgba(255,255,255,0.35);text-transform:uppercase;margin:0 0 12px">
                                Impact Rationale
                            </p>
                            <p style="font-size:14px;line-height:1.75;color:rgba(255,255,255,0.75);margin:0">
                                {{ $incident->impact_rationale }}
                            </p>
                        </div>
                    @endif

                    {{-- Source link --}}
                    @if ($incident->source_link)
                        <div
                            style="padding:16px 20px;background:#0D1627;border:1px solid rgba(255,255,255,0.08);
                            border-radius:10px;display:flex;align-items:center;gap:10px">
                            <svg width="14" height="14" fill="none" stroke="rgba(96,165,250,0.8)"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                <polyline points="15 3 21 3 21 9" />
                                <line x1="10" y1="14" x2="21" y2="3" />
                            </svg>
                            <a href="{{ $incident->source_link }}" target="_blank" rel="noopener noreferrer"
                                style="font-size:13px;color:#93C5FD;text-decoration:none;word-break:break-all"
                                onmouseover="this.style.textDecoration='underline'"
                                onmouseout="this.style.textDecoration='none'">
                                {{ $incident->source_link }}
                            </a>
                        </div>
                    @endif

                </div>

                {{-- RIGHT — fact card --}}
                <div
                    style="background:#0D1627;border:1px solid rgba(255,255,255,0.08);
                        border-radius:12px;padding:20px;position:sticky;top:80px">

                    <p
                        style="font-size:10px;font-weight:900;letter-spacing:0.16em;
                          color:rgba(255,255,255,0.35);text-transform:uppercase;margin:0 0 16px">
                        Incident Details
                    </p>

                    @php
                        $facts = [
                            ['label' => 'State', 'value' => $incident->location],
                            ['label' => 'LGA', 'value' => $incident->lga],
                            ['label' => 'Risk Factor', 'value' => $incident->riskfactors],
                            ['label' => 'Indicator', 'value' => $incident->riskindicators],
                            ['label' => 'Impact Level', 'value' => $incident->impact_label],
                            [
                                'label' => 'Casualties',
                                'value' => $incident->Casualties_count
                                    ? $incident->Casualties_count . ' reported'
                                    : null,
                            ],
                            [
                                'label' => 'Injuries',
                                'value' => $incident->Injuries_count ? $incident->Injuries_count . ' reported' : null,
                            ],
                            ['label' => 'Industry', 'value' => $incident->affected_industry],
                            ['label' => 'Date', 'value' => $incident->formatted_date],
                            ['label' => 'Event ID', 'value' => substr($incident->eventid, 0, 14)],
                        ];
                    @endphp

                    @foreach ($facts as $fact)
                        @if (!empty($fact['value']))
                            <div
                                style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.06);
                                display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
                                <span
                                    style="font-size:10px;font-weight:700;letter-spacing:0.08em;
                                     color:rgba(255,255,255,0.3);text-transform:uppercase;flex-shrink:0;
                                     padding-top:1px">
                                    {{ $fact['label'] }}
                                </span>
                                <span
                                    style="font-size:12px;color:rgba(255,255,255,0.8);text-align:right;word-break:break-word">
                                    {{ $fact['value'] }}
                                </span>
                            </div>
                        @endif
                    @endforeach

                    {{-- Back to hub --}}
                    <a href="{{ route('news') }}"
                        style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:20px;
                          padding:10px;border-radius:8px;background:rgba(255,255,255,0.06);
                          border:1px solid rgba(255,255,255,0.1);font-size:12px;font-weight:600;
                          color:rgba(255,255,255,0.65);text-decoration:none;transition:all 0.15s"
                        onmouseover="this.style.background='rgba(255,255,255,0.1)';this.style.color='#fff'"
                        onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.color='rgba(255,255,255,0.65)'">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path d="m15 18-6-6 6-6" />
                        </svg>
                        Back to Security Hub
                    </a>
                </div>

            </div>
        </div>
    </div>

    <style>
        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.6;
                transform: scale(1.3);
            }
        }

        .alert-grid {
            grid-template-columns: 1fr min(280px, 35%);
        }

        @media (max-width: 720px) {
            .alert-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

</x-layout>
