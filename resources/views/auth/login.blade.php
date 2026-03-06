<x-layout title="Login | Nigeria Risk Index">
    <div class="min-h-screen bg-[#0E1B2C] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-[#1E2D3D] p-10 rounded-3xl border border-white/5 shadow-2xl">
            <div class="text-center">
                <h2 class="text-3xl font-semibold text-white">Welcome Back</h2>
                <p class="mt-2 text-sm text-gray-400">Sign in to access your intelligence dashboard</p>
            </div>

            {{-- Post-reset success message --}}
            @if (session('status'))
                <div
                    class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-sm text-center">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-500 border border-red-500 text-white p-4 rounded-xl text-xs">
                    Invalid credentials. Please check your email and password.
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
                @csrf

                {{-- reCAPTCHA v3: hidden token injected by JS before submit --}}
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-login">

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Email
                            Address</label>
                        <input name="email" type="email" value="{{ old('email') }}" required autofocus
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>

                    {{-- Password field with show/hide toggle --}}
                    <div>
                        <label
                            class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Password</label>
                        <div class="relative">
                            <input name="password" id="login-password" type="password" required
                                class="appearance-none relative block w-full px-4 py-4 pr-12 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                            <button type="button" onclick="togglePassword('login-password', 'eye-login')"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 hover:text-gray-300 transition-colors"
                                tabindex="-1" aria-label="Toggle password visibility">
                                <svg id="eye-login" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-white/10 rounded bg-[#131C27]">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-400">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-400 hover:text-blue-300">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" id="login-btn"
                    class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-500 focus:outline-none transition-all uppercase tracking-widest">
                    Sign In
                </button>
            </form>

            <p class="text-center text-base text-gray-500">
                Don't have an account? <a href="{{ route('register') }}"
                    class="text-blue-400 hover:text-blue-300">Register Now</a>
            </p>
        </div>
    </div>

    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script>
        // ── Show / hide password ───────────────────────────────────────────────
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            // Swap between eye and eye-slash SVG paths
            icon.innerHTML = isHidden ? // Eye-slash (password visible)
                `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.187-3.39M6.53 6.533A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.965 9.965 0 01-4.068 5.189M15 12a3 3 0 01-3.536 2.95M9.88 9.88A3 3 0 0115 12M3 3l18 18" />` : // Eye (password hidden)
                `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        }

        // ── reCAPTCHA + form submit ────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action="{{ route('login') }}"]');
            const btn = document.getElementById('login-btn');
            const siteKey = '{{ config('services.recaptcha.site_key') }}';

            if (!siteKey) {
                console.error('[reCAPTCHA] RECAPTCHA_SITE_KEY is not set in your .env file.');
            }

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                    btn.innerHTML = `
                        <span class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                        SIGNING IN...
                    `;

                    if (!siteKey) {
                        form.submit();
                        return;
                    }

                    grecaptcha.ready(function() {
                        grecaptcha.execute(siteKey, {
                                action: 'login'
                            })
                            .then(function(token) {
                                document.getElementById('g-recaptcha-response-login').value =
                                    token;
                                form.submit();
                            })
                            .catch(function(err) {
                                console.error('[reCAPTCHA] Token fetch failed:', err);
                                btn.disabled = false;
                                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                                btn.innerHTML = 'Sign In';
                            });
                    });
                });
            }
        });
    </script>
</x-layout>
