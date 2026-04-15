{{--
    advisory/partials/tier-lock-prompt.blade.php

    Props:
      $message  — short description of what's locked
      $tier     — minimum tier required (1 or 2)

    Used inside show.blade.php for signals (tier 1) and guidance (tier 2).
--}}
<div class="flex flex-col items-center gap-3 py-2 text-center">
    <div
        class="w-8 h-8 bg-emerald-500/10 border border-emerald-500/20 rounded-full
                flex items-center justify-center">
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
    </div>

    <p class="text-sm text-gray-400">{{ $message }}</p>

    <div class="flex gap-2">
        @guest
            <a href="{{ route('register') }}"
                class="text-xs font-semibold bg-emerald-500 hover:bg-emerald-600
                      text-white px-4 py-2 rounded-lg transition-colors">
                Register Free
            </a>
            <a href="{{ route('login') }}"
                class="text-xs font-semibold bg-white/10 hover:bg-white/15
                      text-white px-4 py-2 rounded-lg transition-colors">
                Sign In
            </a>
        @else
            <a href="{{ route('enterprise-access.create') }}"
                class="text-xs font-semibold bg-emerald-500 hover:bg-emerald-600
                      text-white px-4 py-2 rounded-lg transition-colors">
                Upgrade to {{ $tier >= 2 ? 'Premium' : 'Free Tier' }}
            </a>
        @endguest
    </div>
</div>
