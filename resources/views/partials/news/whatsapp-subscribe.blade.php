{{--
    WhatsApp Alert Subscription Widget
    File: resources/views/partials/news/whatsapp-subscribe.blade.php

    Include this anywhere on the news page, e.g. just before the incidents table:
        @include('partials.news.whatsapp-subscribe')
--}}

<section class="max-w-7xl mx-auto px-6 lg:px-16 pb-10">
    <div class="rounded-2xl border border-green-100 bg-white shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
                {{-- WhatsApp logo SVG --}}
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M20.52 3.48A11.93 11.93 0 0 0 12 0C5.37 0 0 5.37 0 12c0 2.11.55 4.16 1.6 5.97L0 24l6.19-1.62A11.93 11.93 0 0 0 12 24c6.63 0 12-5.37 12-12 0-3.21-1.25-6.22-3.48-8.52Zm-8.52 18.43a9.9 9.9 0 0 1-5.04-1.37l-.36-.21-3.73.98.99-3.62-.24-.37A9.9 9.9 0 0 1 2.1 12 9.9 9.9 0 0 1 12 2.1 9.9 9.9 0 0 1 21.9 12a9.9 9.9 0 0 1-9.9 9.91Zm5.44-7.41c-.3-.15-1.76-.87-2.03-.97-.28-.1-.48-.15-.68.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.78-1.66-2.08-.17-.3-.02-.46.13-.61.13-.13.3-.35.44-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.68-1.63-.93-2.23-.24-.59-.49-.51-.68-.52-.17-.01-.37-.01-.57-.01-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.22 3.08.15.2 2.1 3.2 5.09 4.49.71.31 1.27.49 1.7.63.72.23 1.37.2 1.89.12.58-.09 1.76-.72 2.01-1.41.25-.69.25-1.28.17-1.41-.08-.13-.28-.2-.58-.35Z"
                        fill="#25D366" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-primary">Get Instant Security Alerts on WhatsApp</h3>
                <p class="text-xs text-gray-500 mt-0.5">Receive immediate notifications when high-risk incidents are
                    recorded across Nigeria.</p>
            </div>
        </div>

        {{-- Status messages --}}
        @if (session('whatsapp_success'))
            <div
                class="mx-6 mt-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 font-medium">
                ✅ {{ session('whatsapp_success') }}
            </div>
        @endif
        @if (session('whatsapp_error'))
            <div
                class="mx-6 mt-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 font-medium">
                ⚠ {{ session('whatsapp_error') }}
            </div>
        @endif
        @if (session('whatsapp_info'))
            <div
                class="mx-6 mt-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700 font-medium">
                ℹ {{ session('whatsapp_info') }}
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('whatsapp.subscribe') }}" method="POST" class="px-6 py-5">
            @csrf

            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Phone number --}}
                <div class="flex-1">
                    <label for="wa_phone" class="block text-xs font-medium text-gray-500 mb-1">
                        WhatsApp Number <span class="text-red-400">*</span>
                    </label>
                    <input id="wa_phone" type="tel" name="phone_number" placeholder="+2348012345678"
                        value="{{ old('phone_number') }}" required
                        class="w-full text-sm rounded-lg border @error('phone_number') border-red-400 @else border-gray-200 @enderror
                                  px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-300
                                  text-primary placeholder-gray-400">
                    @error('phone_number')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Name (optional) --}}
                <div class="sm:w-44">
                    <label for="wa_name" class="block text-xs font-medium text-gray-500 mb-1">
                        First Name <span class="text-gray-300">(optional)</span>
                    </label>
                    <input id="wa_name" type="text" name="name" placeholder="e.g. Freddie"
                        value="{{ old('name') }}"
                        class="w-full text-sm rounded-lg border border-gray-200
                                  px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-300
                                  text-primary placeholder-gray-400">
                </div>

                {{-- Alert level --}}
                <div class="sm:w-44">
                    <label for="wa_tier" class="block text-xs font-medium text-gray-500 mb-1">Alert Level</label>
                    <select id="wa_tier" name="subscription_tier"
                        class="w-full text-sm rounded-lg border border-gray-200
                                   px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-300
                                   text-primary bg-white">
                        <option value="all" @selected(old('subscription_tier', 'all') === 'all')>High + Critical</option>
                        <option value="critical" @selected(old('subscription_tier') === 'critical')>Critical only</option>
                    </select>
                </div>

                {{-- Submit --}}
                <div class="sm:self-end">
                    <button type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-green-500 hover:bg-green-600
                                   text-white text-sm font-semibold transition-colors shadow-sm
                                   focus:outline-none focus:ring-2 focus:ring-green-400">
                        Subscribe
                    </button>
                </div>
            </div>

            <p class="mt-3 text-[11px] text-gray-400 leading-relaxed">
                We'll send a WhatsApp confirmation message. Reply <strong>YES</strong> to activate.
                Reply <strong>STOP</strong> at any time to unsubscribe. Nigerian numbers (+234) only.
            </p>
        </form>

    </div>
</section>
