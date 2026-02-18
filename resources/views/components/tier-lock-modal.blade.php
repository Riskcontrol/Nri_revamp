@php
    $tierLock = session('tier_lock');
@endphp

<div id="tierLockModal"
    class="fixed inset-0 z-[9999] hidden bg-black/60 backdrop-blur-sm px-4
            items-start pt-24 sm:items-center sm:pt-0 justify-center">

    <div class="w-full max-w-md rounded-2xl bg-[#0F1720] border border-white/10 shadow-2xl overflow-hidden">
        <div class="p-5 border-b border-white/10 flex items-start justify-between gap-4">
            <div>
                <h3 id="tierLockTitle" class="text-white text-lg font-semibold">Locked</h3>
                <p id="tierLockSubtitle" class="text-gray-300 text-sm mt-1">
                    This option is not available on your plan.
                </p>
            </div>

            <button id="tierLockClose" class="text-gray-400 hover:text-white transition" aria-label="Close">✕</button>
        </div>

        <div class="p-5 space-y-3">
            <p id="tierLockMessage" class="text-gray-200 text-sm"></p>

            <div class="rounded-xl bg-white/5 border border-white/10 p-3 text-sm text-gray-200">
                <div class="flex items-center justify-between">
                    <span id="tierLockLabel1" class="text-gray-400">Locked item</span>
                    <span id="tierLockLocation" class="font-semibold"></span>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <span id="tierLockLabel2" class="text-gray-400">Next switch</span>
                    <span id="tierLockWhen" class="font-semibold"></span>
                </div>
            </div>

            <div id="tierLockFooterText" class="text-xs text-gray-400">
                Upgrade to unlock premium access.
            </div>
        </div>

        <div class="p-5 border-t border-white/10 flex gap-3">
            <button id="tierLockOk"
                class="flex-1 rounded-lg bg-white/10 hover:bg-white/15 text-white text-sm font-semibold py-2 transition">
                Okay
            </button>

            <a id="tierLockCta" href="{{ route('enterprise-access.create') }}"
                class="flex-1 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2 transition text-center">
                Contact for Premium
            </a>
        </div>
    </div>
</div>

<script>
    window.__TIER_LOCK_FLASH__ = @json($tierLock);
</script>
