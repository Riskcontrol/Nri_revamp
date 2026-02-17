<x-layout title="Request Enterprise Access">
    <div class="min-h-screen bg-gradient-to-b from-[#070B12] via-[#0B1220] to-[#070B12]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-white">

            {{-- Header --}}
            <div class="mb-8">
                <p class="text-xs font-semibold tracking-wider text-emerald-300 uppercase">
                    Enterprise onboarding
                </p>

                <h1 class="mt-2 text-3xl sm:text-4xl font-semibold tracking-tight text-white">
                    Request Enterprise Access
                </h1>

                <p class="mt-3 text-sm sm:text-base text-slate-300">
                    Tell us what you need, and we’ll recommend the right plan and onboarding path.
                </p>

                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="text-xs px-3 py-1 rounded-full bg-white/5 text-slate-200 border border-white/10">
                        Premium onboarding
                    </span>
                    <span class="text-xs px-3 py-1 rounded-full bg-white/5 text-slate-200 border border-white/10">
                        API integration
                    </span>
                    <span class="text-xs px-3 py-1 rounded-full bg-white/5 text-slate-200 border border-white/10">
                        Custom reports
                    </span>
                    <span class="text-xs px-3 py-1 rounded-full bg-white/5 text-slate-200 border border-white/10">
                        Bulk exports
                    </span>
                </div>
            </div>

            {{-- Alerts --}}
            @if (session('success'))
                <div class="mt-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-200">
                    <p class="font-semibold">Please fix the errors below.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('enterprise-access.store') }}" class="mt-8 space-y-8">
                @csrf

                {{-- Attribution (from tier-lock CTA query params) --}}
                <input type="hidden" name="source_page" value="{{ old('source_page', $source ?? null) }}">
                <input type="hidden" name="attempted_risk_type"
                    value="{{ old('attempted_risk_type', $risk ?? null) }}">
                <input type="hidden" name="attempted_year" value="{{ old('attempted_year', $year ?? null) }}">

                @php
                    $field = 'w-full rounded-xl bg-white/5 border border-white/10 px-3 py-2.5 text-sm text-white shadow-sm
                         placeholder:text-slate-400
                         focus:outline-none focus:ring-4 focus:ring-emerald-500/15 focus:border-emerald-400';
                    $select = 'w-full rounded-xl bg-white/5 border border-white/10 px-3 py-2.5 text-sm text-white shadow-sm
                         focus:outline-none focus:ring-4 focus:ring-emerald-500/15 focus:border-emerald-400';
                @endphp

                {{-- A) Organization --}}
                <section class="rounded-2xl bg-[#0F1720]/80 border border-white/10 shadow-2xl shadow-black/20 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Organization Information</h2>
                            <p class="mt-1 text-sm text-slate-300">
                                Help us understand who you are and what environment you operate in.
                            </p>
                        </div>
                        <span
                            class="shrink-0 text-xs font-semibold text-slate-200 bg-white/5 border border-white/10 rounded-full px-3 py-1">
                            Step 1
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-200">Organization Name*</label>
                            <input name="organization_name" value="{{ old('organization_name') }}"
                                class="mt-1 {{ $field }}" placeholder="e.g. Acme Security Ltd" required>
                            @error('organization_name')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-200">Organization Type*</label>
                            @php
                                $types = [
                                    'Corporate/Private Sector',
                                    'International NGO',
                                    'Local NGO',
                                    'Government Agency',
                                    'Media/Journalism',
                                    'Academic/Research',
                                    'Consulting Firm',
                                    'Financial Institution',
                                    'Other',
                                ];
                            @endphp

                            {{-- NOTE: inline style helps some browsers render option text properly --}}
                            <select id="orgType" name="organization_type" class="mt-1 {{ $select }}"
                                style="background-color: rgba(255,255,255,0.05); color: #fff;" required>
                                <option value="" class="bg-[#0B1220] text-slate-200">Select...</option>
                                @foreach ($types as $t)
                                    <option value="{{ $t }}" @selected(old('organization_type') === $t)
                                        class="bg-[#0B1220] text-white">
                                        {{ $t }}
                                    </option>
                                @endforeach
                            </select>

                            @error('organization_type')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="industryWrap" class="hidden sm:col-span-2">
                            <label class="text-xs font-medium text-slate-200">Industry Sector* (Corporate)</label>
                            <input name="industry_sector" value="{{ old('industry_sector') }}"
                                class="mt-1 {{ $field }}"
                                placeholder="e.g. Fintech, Oil & Gas, Logistics, Manufacturing">
                            @error('industry_sector')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-200">Company Size</label>
                            <select name="company_size" class="mt-1 {{ $select }}"
                                style="background-color: rgba(255,255,255,0.05); color: #fff;">
                                <option value="" class="bg-[#0B1220] text-slate-200">Select...</option>
                                @foreach (['1-50 employees', '51-200 employees', '201-1000 employees', '1000+ employees'] as $s)
                                    <option value="{{ $s }}" @selected(old('company_size') === $s)
                                        class="bg-[#0B1220] text-white">
                                        {{ $s }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_size')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- B) Use Case --}}
                <section class="rounded-2xl bg-[#0F1720]/80 border border-white/10 shadow-2xl shadow-black/20 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Use Case & Needs</h2>
                            <p class="mt-1 text-sm text-slate-300">
                                Tell us what you’re trying to achieve so we can tailor your setup.
                            </p>
                        </div>
                        <span
                            class="shrink-0 text-xs font-semibold text-slate-200 bg-white/5 border border-white/10 rounded-full px-3 py-1">
                            Step 2
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-200">Primary Use Case*</label>
                            @php
                                $useCases = [
                                    'Investment risk assessment',
                                    'Operational security planning',
                                    'Supply chain risk management',
                                    'Personnel safety/travel security',
                                    'Crisis monitoring & response',
                                    'Research & analysis',
                                    'Due diligence',
                                    'Insurance underwriting',
                                    'Other',
                                ];
                            @endphp
                            <select id="useCase" name="primary_use_case" class="mt-1 {{ $select }}"
                                style="background-color: rgba(255,255,255,0.05); color: #fff;" required>
                                <option value="" class="bg-[#0B1220] text-slate-200">Select...</option>
                                @foreach ($useCases as $u)
                                    <option value="{{ $u }}" @selected(old('primary_use_case') === $u)
                                        class="bg-[#0B1220] text-white">
                                        {{ $u }}
                                    </option>
                                @endforeach
                            </select>
                            @error('primary_use_case')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="useCaseOtherWrap" class="hidden">
                            <label class="text-xs font-medium text-slate-200">Other (please specify)*</label>
                            <input name="primary_use_case_other" value="{{ old('primary_use_case_other') }}"
                                class="mt-1 {{ $field }}" placeholder="Describe your use case">
                            @error('primary_use_case_other')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-200">Geographic Focus* (select all that
                                apply)</label>
                            @php
                                $geoOptions = [
                                    'Specific states',
                                    'Specific sectors/regions',
                                    'Nationwide coverage',
                                    'Specific cities/LGAs',
                                ];
                                $oldGeo = old('geographic_focus', []);
                            @endphp

                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($geoOptions as $g)
                                    <label
                                        class="flex items-start gap-3 rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm
                                               hover:border-emerald-400/30 hover:bg-emerald-500/5 transition">
                                        <input type="checkbox" name="geographic_focus[]" value="{{ $g }}"
                                            @checked(in_array($g, $oldGeo))
                                            class="mt-0.5 h-4 w-4 rounded border-white/20 text-emerald-400 focus:ring-emerald-400/30">
                                        <span class="text-slate-200">{{ $g }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @error('geographic_focus')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="statesWrap" class="hidden">
                            <label class="text-xs font-medium text-slate-200">Which states?</label>
                            <textarea id="statesInput" class="mt-1 {{ $field }}"
                                placeholder="Enter states separated by commas (e.g. Lagos, Kano, Rivers)">{{ old('statesInput') }}</textarea>
                            <p class="text-[11px] text-slate-400 mt-1">We’ll parse this into a list.</p>
                        </div>

                        <div id="sectorsWrap" class="hidden">
                            <label class="text-xs font-medium text-slate-200">Specific sectors/regions</label>
                            <textarea name="focus_sectors_regions" class="mt-1 {{ $field }}"
                                placeholder="e.g. oil & gas regions, border areas, corridors...">{{ old('focus_sectors_regions') }}</textarea>
                            @error('focus_sectors_regions')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="citiesWrap" class="hidden">
                            <label class="text-xs font-medium text-slate-200">Specific cities/LGAs</label>
                            <textarea name="focus_cities_lgas" class="mt-1 {{ $field }}" placeholder="List key cities/LGAs...">{{ old('focus_cities_lgas') }}</textarea>
                            @error('focus_cities_lgas')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-200">Features of Interest* (select all that
                                apply)</label>
                            @php
                                $features = [
                                    'Real-time incident tracking',
                                    'Predictive risk analytics',
                                    'Custom report generation',
                                    'API integration',
                                    'Bulk data exports',
                                ];
                                $oldFeatures = old('features_of_interest', []);
                            @endphp

                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($features as $f)
                                    <label
                                        class="flex items-start gap-3 rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-sm
                                               hover:border-emerald-400/30 hover:bg-emerald-500/5 transition">
                                        <input type="checkbox" name="features_of_interest[]"
                                            value="{{ $f }}" @checked(in_array($f, $oldFeatures))
                                            class="mt-0.5 h-4 w-4 rounded border-white/20 text-emerald-400 focus:ring-emerald-400/30">
                                        <span class="text-slate-200">{{ $f }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @error('features_of_interest')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- C) Contact --}}
                <section class="rounded-2xl bg-[#0F1720]/80 border border-white/10 shadow-2xl shadow-black/20 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Contact Details</h2>
                            <p class="mt-1 text-sm text-slate-300">
                                We’ll reach out to schedule a demo or onboarding call.
                            </p>
                        </div>
                        <span
                            class="shrink-0 text-xs font-semibold text-slate-200 bg-white/5 border border-white/10 rounded-full px-3 py-1">
                            Step 3
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-200">Name*</label>
                            <input name="contact_name" value="{{ old('contact_name') }}"
                                class="mt-1 {{ $field }}" placeholder="Your full name" required>
                            @error('contact_name')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-200">Email*</label>
                            <input name="contact_email" type="email" value="{{ old('contact_email') }}"
                                class="mt-1 {{ $field }}" placeholder="name@company.com" required>
                            @error('contact_email')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-200">Phone*</label>
                            <input name="contact_phone" value="{{ old('contact_phone') }}"
                                class="mt-1 {{ $field }}" placeholder="+234..." required>
                            @error('contact_phone')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-200">Preferred contact method*</label>
                            <select name="preferred_contact_method" class="mt-1 {{ $select }}"
                                style="background-color: rgba(255,255,255,0.05); color: #fff;" required>
                                <option value="" class="bg-[#0B1220] text-slate-200">Select...</option>
                                @foreach (['Email', 'Phone call', 'WhatsApp'] as $m)
                                    <option value="{{ $m }}" @selected(old('preferred_contact_method') === $m)
                                        class="bg-[#0B1220] text-white">
                                        {{ $m }}
                                    </option>
                                @endforeach
                            </select>
                            @error('preferred_contact_method')
                                <p class="text-xs text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col sm:flex-row gap-3">
                        <button
                            class="inline-flex items-center justify-center rounded-xl bg-emerald-500 hover:bg-emerald-600
                                   px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition
                                   focus:outline-none focus:ring-4 focus:ring-emerald-400/20">
                            Submit Request
                        </button>

                        <a href="/"
                            class="inline-flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10
                                   border border-white/10 px-5 py-2.5 text-sm font-semibold text-slate-200 transition">
                            Cancel
                        </a>
                    </div>

                    <p class="mt-4 text-xs text-slate-400">
                        By submitting, you consent to being contacted about onboarding and product access.
                    </p>
                </section>
            </form>

            {{-- Logic --}}
            <script>
                (function() {
                    const orgType = document.getElementById('orgType');
                    const industryWrap = document.getElementById('industryWrap');

                    const useCase = document.getElementById('useCase');
                    const useCaseOtherWrap = document.getElementById('useCaseOtherWrap');

                    const statesWrap = document.getElementById('statesWrap');
                    const sectorsWrap = document.getElementById('sectorsWrap');
                    const citiesWrap = document.getElementById('citiesWrap');

                    function getCheckedGeo() {
                        return Array.from(document.querySelectorAll('input[name="geographic_focus[]"]:checked'))
                            .map(i => i.value);
                    }

                    function syncConditionalUI() {
                        const t = orgType?.value || '';
                        industryWrap?.classList.toggle('hidden', t !== 'Corporate/Private Sector');

                        const u = useCase?.value || '';
                        useCaseOtherWrap?.classList.toggle('hidden', u !== 'Other');

                        const geo = getCheckedGeo();
                        statesWrap?.classList.toggle('hidden', !geo.includes('Specific states'));
                        sectorsWrap?.classList.toggle('hidden', !geo.includes('Specific sectors/regions'));
                        citiesWrap?.classList.toggle('hidden', !geo.includes('Specific cities/LGAs'));
                    }

                    orgType?.addEventListener('change', syncConditionalUI);
                    useCase?.addEventListener('change', syncConditionalUI);
                    document.querySelectorAll('input[name="geographic_focus[]"]').forEach(cb => cb.addEventListener('change',
                        syncConditionalUI));

                    // Parse "statesInput" into focus_states[] on submit
                    const form = document.querySelector('form');
                    const statesInput = document.getElementById('statesInput');

                    form?.addEventListener('submit', function() {
                        document.querySelectorAll('input[name="focus_states[]"]').forEach(el => el.remove());

                        const geo = getCheckedGeo();
                        if (geo.includes('Specific states')) {
                            const raw = (statesInput?.value || '').split(',')
                                .map(s => s.trim())
                                .filter(Boolean);

                            raw.forEach(s => {
                                const i = document.createElement('input');
                                i.type = 'hidden';
                                i.name = 'focus_states[]';
                                i.value = s;
                                form.appendChild(i);
                            });
                        }
                    });

                    syncConditionalUI();
                })();
            </script>

        </div>
    </div>
</x-layout>
