<x-layout title="Register | Nigeria Risk Index">
    <div class="min-h-screen bg-[#0E1B2C] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-[#1E2D3D] p-10 rounded-3xl border border-white/5 shadow-2xl">
            <div class="text-center">
                <h2 class="text-3xl font-semibold text-white">Access the Hub</h2>
            </div>

            @if ($errors->any())
                <div class="bg-red-500 border border-red-500 text-white p-4 rounded-xl mb-6 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-8 space-y-6" id="register-form" action="{{ route('register') }}" method="POST">
                @csrf

                {{-- reCAPTCHA v3 token (injected by JS before submit) --}}
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                {{-- Honeypot: hidden from humans, bots fill it, server rejects --}}
                <input type="text" name="website" value=""
                    style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Full
                            Name</label>
                        <input name="name" type="text" required value="{{ old('name') }}"
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Email</label>
                        <input name="email" type="email" required value="{{ old('email') }}"
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">
                            Organization Type
                        </label>
                        <select name="organization" id="organization" required
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                            <option value="" disabled {{ old('organization') ? '' : 'selected' }}>Select an option
                            </option>
                            @foreach (['Individual', 'Corporate/Private Sector', 'International NGO', 'Local NGO', 'Government Agency', 'Media/Journalism', 'Academic/Research', 'Consulting Firm', 'Financial Institution', 'Other'] as $option)
                                <option value="{{ $option }}"
                                    {{ old('organization') === $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Only shown when "Other" is selected --}}
                        <div id="org_other_wrap" class="mt-3 hidden">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Specify
                                (Other)</label>
                            <input name="organization_other" id="organization_other" type="text"
                                value="{{ old('organization_other') }}" placeholder="Type your organization"
                                class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                        </div>
                    </div>

                    {{-- Password with eye toggle --}}
                    <div>
                        <label
                            class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Password</label>
                        <div class="relative">
                            <input name="password" id="reg-password" type="password" required
                                class="appearance-none relative block w-full px-4 py-4 pr-12 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                            <button type="button" onclick="togglePassword('reg-password', 'eye-reg-password')"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 hover:text-gray-300 transition-colors"
                                tabindex="-1" aria-label="Toggle password visibility">
                                <svg id="eye-reg-password" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Confirm Password with eye toggle --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Confirm
                            Password</label>
                        <div class="relative">
                            <input name="password_confirmation" id="reg-password-confirm" type="password" required
                                class="appearance-none relative block w-full px-4 py-4 pr-12 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                            <button type="button" onclick="togglePassword('reg-password-confirm', 'eye-reg-confirm')"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 hover:text-gray-300 transition-colors"
                                tabindex="-1" aria-label="Toggle confirm password visibility">
                                <svg id="eye-reg-confirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" id="register-btn"
                    class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 focus:outline-none transition-all uppercase tracking-widest">
                    Register
                </button>
            </form>

            <p class="text-center text-base text-gray-500">
                Already have access? <a href="/login" class="text-blue-400 hover:text-blue-300">Login</a>
            </p>
        </div>
    </div>

    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script>
        // ── Show / hide password (shared with login.blade.php) ────────────────
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            icon.innerHTML = isHidden ? // Eye-slash: password is now visible
                `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.187-3.39M6.53 6.533A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.965 9.965 0 01-4.068 5.189M15 12a3 3 0 01-3.536 2.95M9.88 9.88A3 3 0 0115 12M3 3l18 18" />` : // Eye: password is now hidden
                `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const orgSelect = document.getElementById('organization');
            const otherWrap = document.getElementById('org_other_wrap');
            const otherInput = document.getElementById('organization_other');

            // ── Organization "Other" toggle ────────────────────────────────────
            function toggleOther() {
                const isOther = orgSelect && orgSelect.value === 'Other';
                if (!otherWrap || !otherInput) return;
                otherWrap.classList.toggle('hidden', !isOther);
                otherInput.required = isOther;
                if (!isOther) otherInput.value = '';
            }

            if (orgSelect) {
                orgSelect.addEventListener('change', toggleOther);
                toggleOther();
            }

            // ── Form submit: get reCAPTCHA token, THEN submit ──────────────────
            const registerForm = document.getElementById('register-form');
            const submitBtn = document.getElementById('register-btn');
            const siteKey = '{{ config('services.recaptcha.site_key') }}';
            const isLocal = {{ app()->environment('local') ? 'true' : 'false' }};

            if (!siteKey) {
                console.error('[reCAPTCHA] RECAPTCHA_SITE_KEY is not set in your .env file.');
            }

            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    submitBtn.innerHTML = `
                        <span class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                        Registering...
                    `;

                    // On local dev, skip reCAPTCHA entirely — Google always returns
                    // score 0.0 / success:false for localhost tokens, which would
                    // block every registration attempt locally.
                    if (!siteKey || isLocal) {
                        registerForm.submit();
                        return;
                    }

                    grecaptcha.ready(function() {
                        grecaptcha.execute(siteKey, {
                                action: 'register'
                            })
                            .then(function(token) {
                                document.getElementById('g-recaptcha-response').value = token;
                                registerForm.submit();
                            })
                            .catch(function(err) {
                                console.error('[reCAPTCHA] Token fetch failed:', err);
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                submitBtn.innerHTML = 'Register';
                            });
                    });
                });
            }
        });
    </script>
</x-layout>
