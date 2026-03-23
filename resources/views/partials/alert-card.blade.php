{{--
    partials/alert-card.blade.php
    Variables expected:
      $alert  — stdClass row from SecurityHubController::fetchActiveAlerts()
      $cfg    — array from $levelConfig keyed by $alert->impact_label
      $i      — integer index for openAlertModal(i)
--}}

{{-- Card body --}}
<div class="flex flex-col flex-1 p-5">

    {{-- Impact badge --}}
    <div class="mb-2">
        <span
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                     text-[10px] font-black uppercase tracking-widest
                     {{ $cfg['badge_bg'] }} {{ $cfg['badge_text'] }}">
            <span class="w-1.5 h-1.5 rounded-full bg-white/70 inline-block"></span>
            {{ $alert->impact_label }}
        </span>
    </div>

    {{-- Contextual headline sentence — e.g. "Kidnapping in Zamfara, Anka" --}}
    <p class="text-white/50 text-[11px] font-medium tracking-wide mb-3 truncate">
        {{ $alert->header_fragment }}
    </p>

    {{-- Card title (caption or truncated add_notes) --}}
    <h3 class="text-white font-bold text-[15px] leading-snug mb-3">
        {{ $alert->card_title }}
    </h3>

    {{-- Location --}}
    <div class="flex items-center gap-1.5 text-gray-400 text-[12px] mb-1">
        <svg class="w-3 h-3 {{ $cfg['icon_color'] }} flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z" />
            <circle cx="12" cy="10" r="3" />
        </svg>
        <span class="truncate">{{ $alert->location_display }}</span>
    </div>

    {{-- Date (eventdateToUse — no time stored) --}}
    <div class="flex items-center gap-1.5 text-gray-400 text-[12px] mb-4">
        <svg class="w-3 h-3 {{ $cfg['icon_color'] }} flex-shrink-0" fill="none" stroke="currentColor"
            stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
        </svg>
        {{ $alert->formatted_date }}
    </div>

    {{-- Summary — add_notes --}}
    <p class="text-gray-400 text-[12.5px] leading-relaxed line-clamp-3 flex-1">
        {{ $alert->add_notes ?: 'No summary available.' }}
    </p>
</div>

{{-- View Details button --}}
<div class="px-5 pb-5">
    <button type="button" onclick="openAlertModal({{ $i }})"
        class="w-full py-2.5 rounded-xl text-[11px] font-black uppercase tracking-widest
                   transition-all duration-200 active:scale-95
                   {{ $cfg['btn_bg'] }} {{ $cfg['btn_text'] }}">
        View Details
    </button>
</div>
