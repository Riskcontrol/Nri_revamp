<footer class="bg-[#0a1628] text-white border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6 md:px-8 py-16">

        <div class="grid gap-12 lg:grid-cols-4 items-start">

            {{-- Brand Column --}}
            <div class="flex flex-col">
                <a href="{{ url('/') }}" class="block mb-8 -mt-2 group">
                    <img src="{{ asset('images/nri-logo-white.png') }}" alt="Nigeria Risk Index Logo"
                        class="h-16 w-auto object-contain transition-transform group-hover:scale-105" />
                </a>
                <p class="text-base leading-relaxed text-gray-300 max-w-xs mb-8">
                    NRI supports safer decisions through data-driven risk intelligence.
                </p>
                <div class="flex gap-3">
                    @php
                        $socials = [
                            ['icon' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'],
                            ['icon' => 'fa-brands fa-x-twitter', 'label' => 'X'],
                            ['icon' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'],
                            ['icon' => 'fa-brands fa-instagram', 'label' => 'Instagram'],
                        ];
                    @endphp
                    @foreach ($socials as $social)
                        <a href="#"
                            class="h-10 w-10 flex items-center justify-center rounded-lg bg-white/5 border border-white/10 text-gray-300 hover:bg-blue-600 hover:text-white transition-all">
                            <i class="{{ $social['icon'] }} text-sm"></i>
                            <span class="sr-only">{{ $social['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Use Cases --}}
            <div>
                <h3 class="text-sm font-semibold tracking-[0.15em] text-white uppercase mb-8">Use Cases</h3>
                <ul class="space-y-4 text-base">
                    <li><a class="text-gray-400 hover:text-white transition-colors"
                            href="{{ route('locationIntelligence', ['state' => 'Lagos']) }}">Location Intelligence</a>
                    </li>
                    <li><a class="text-gray-400 hover:text-white transition-colors"
                            href="{{ route('securityIntelligence') }}">Risk Database</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors"
                            href="{{ route('insights.index') }}">Business Insight</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors"
                            href="{{ route('reports.index') }}">Custom Reports</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div>
                <h3 class="text-sm font-semibold tracking-[0.15em] text-white uppercase mb-8">Company</h3>
                <ul class="space-y-4 text-base">
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">About Us</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors" href="#">Methodology</a></li>
                    <li><a class="text-gray-400 hover:text-white transition-colors"
                            href="{{ route('enterprise-access.create') }}">Contact</a></li>
                </ul>
            </div>

            {{-- Newsletter & Contact --}}
            <div x-data="newsletterWidget()" x-init="initFromUrl()" class="space-y-8">
                <div>
                    <h3 class="text-sm font-semibold tracking-[0.15em] text-white uppercase mb-6">Stay Updated</h3>
                    <p class="text-gray-400 mb-6">Get the latest risk alerts in your inbox.</p>

                    <div class="flex items-center overflow-hidden rounded-lg border border-white/10 bg-white/5 focus-within:border-blue-500 transition-all"
                        :class="{ 'border-red-500/50 focus-within:border-red-500': error }">
                        <input type="email" x-model="email" @keydown.enter="submit()" placeholder="Enter your email"
                            class="w-full bg-transparent px-4 py-3 text-sm text-white outline-none placeholder:text-gray-500" />
                        <button @click="submit()" :disabled="loading"
                            class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed px-6 py-3 text-sm font-bold text-white transition-colors whitespace-nowrap flex items-center gap-2">
                            <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            <span x-text="loading ? 'Sending…' : 'Subscribe'"></span>
                        </button>
                    </div>
                    <p x-show="error" x-cloak x-text="error" class="mt-2 text-xs text-red-400"></p>
                </div>

                {{-- Contact Info --}}
                <ul class="space-y-4">
                    <li class="flex items-center gap-3 text-gray-400 group">
                        <i class="fa-solid fa-envelope text-gray-500 group-hover:text-blue-500 transition-colors"></i>
                        <a href="mailto:info@riskcontrolnigeria.com"
                            class="hover:text-white transition-colors">info@riskcontrolnigeria.com</a>
                    </li>
                    <li class="flex items-center gap-3 text-gray-400">
                        <i class="fa-solid fa-location-dot text-gray-500"></i>
                        <span>Lagos, Nigeria</span>
                    </li>
                </ul>

                {{-- ── Modal (teleported to <body> to avoid z-index/overflow issues) ──────── --}}
                <template x-teleport="body">
                    <div x-show="modal.open" x-cloak x-transition.opacity @keydown.escape.window="modal.open = false"
                        class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                        {{-- Backdrop --}}
                        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modal.open = false"></div>

                        {{-- Panel --}}
                        <div x-show="modal.open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="relative w-full max-w-md bg-[#0f1f3d] border border-white/10 rounded-2xl shadow-2xl p-8 text-center">
                            {{-- Close button --}}
                            <button @click="modal.open = false"
                                class="absolute top-4 right-4 text-gray-500 hover:text-white transition-colors">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>

                            {{-- State icon --}}
                            <div class="mx-auto mb-6 h-16 w-16 flex items-center justify-center rounded-full"
                                :class="{
                                    'bg-blue-500/10': modal.type === 'pending',
                                    'bg-green-500/10': modal.type === 'confirmed' || modal.type === 'already',
                                    'bg-gray-500/10': modal.type === 'unsubscribed',
                                    'bg-red-500/10': modal.type === 'error',
                                }">
                                <i class="text-2xl"
                                    :class="{
                                        'fa-solid fa-envelope-circle-check text-blue-400': modal.type === 'pending',
                                        'fa-solid fa-circle-check text-green-400': modal.type === 'confirmed' || modal
                                            .type === 'already',
                                        'fa-solid fa-circle-minus text-gray-400': modal.type === 'unsubscribed',
                                        'fa-solid fa-circle-exclamation text-red-400': modal.type === 'error',
                                    }">
                                </i>
                            </div>

                            <h2 class="text-xl font-bold text-white mb-3" x-text="modal.title"></h2>
                            <p class="text-gray-400 text-sm leading-relaxed mb-8" x-text="modal.message"></p>

                            <button @click="modal.open = false"
                                class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition-colors">
                                Got it
                            </button>
                        </div>
                    </div>
                </template>
            </div>

        </div>

        {{-- Bottom Bar --}}
        <div
            class="mt-20 pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-6 text-sm text-gray-500">
            <p>&copy; {{ now()->year }} Nigeria Risk Index. All rights reserved.</p>
            <nav class="flex gap-8">
                <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                <a href="#" class="hover:text-white transition-colors">Cookie Settings</a>
            </nav>
        </div>
    </div>
</footer>

<script>
    function newsletterWidget() {
        return {
            email: '',
            loading: false,
            error: '',
            modal: {
                open: false,
                type: '',
                title: '',
                message: ''
            },

            showModal(type, title, message) {
                this.modal = {
                    open: true,
                    type,
                    title,
                    message
                };
            },

            async submit() {
                this.error = '';
                const emailVal = this.email.trim();

                if (!emailVal) {
                    this.error = 'Please enter your email address.';
                    return;
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                    this.error = 'Please enter a valid email address.';
                    return;
                }

                this.loading = true;
                try {
                    const res = await fetch('{{ route('newsletter.subscribe') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            email: emailVal
                        }),
                    });

                    const data = await res.json();

                    if (res.ok) {
                        this.email = '';
                        if (data.status === 'already_confirmed') {
                            this.showModal(
                                'already',
                                'Already subscribed!',
                                "This email is already on our list. You'll keep receiving the latest Nigeria Risk Index alerts."
                            );
                        } else {
                            this.showModal(
                                'pending',
                                'Check your inbox!',
                                'We sent a confirmation link to ' + emailVal +
                                '. Click it to activate your subscription.'
                            );
                        }
                    } else if (res.status === 422) {
                        const firstError = Object.values(data.errors || {})[0]?.[0];
                        this.error = firstError || 'Please enter a valid email address.';
                    } else {
                        this.showModal('error', 'Something went wrong', data.message ||
                            'Please try again in a moment.');
                    }
                } catch (e) {
                    this.showModal('error', 'Connection error',
                        'Unable to reach the server. Please check your connection and try again.');
                } finally {
                    this.loading = false;
                }
            },

            // Reads ?newsletter=confirmed or ?newsletter=unsubscribed appended by
            // the controller redirect, shows the modal, then cleans the URL.
            initFromUrl() {
                const params = new URLSearchParams(window.location.search);
                const state = params.get('newsletter');
                if (!state) return;

                window.history.replaceState({}, '', window.location.pathname + window.location.hash);

                if (state === 'confirmed') {
                    this.showModal(
                        'confirmed',
                        "You're subscribed! 🎉",
                        "Welcome to Nigeria Risk Index alerts. You'll be the first to know about emerging risks across Nigeria."
                    );
                } else if (state === 'unsubscribed') {
                    this.showModal(
                        'unsubscribed',
                        'Unsubscribed',
                        "You've been removed from our mailing list. You won't receive any further emails from us."
                    );
                }
            },
        };
    }
</script>
