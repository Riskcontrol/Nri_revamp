<x-layout title="Reset Password | Nigeria Risk Index">
    <div class="min-h-screen bg-[#0E1B2C] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-[#1E2D3D] p-10 rounded-3xl border border-white/5 shadow-2xl">

            {{-- Header --}}
            <div class="text-center">
                <h2 class="text-3xl font-semibold text-white">New Password</h2>
                <p class="mt-2 text-sm text-gray-400">
                    Choose a strong password for your account.
                </p>
            </div>

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="bg-red-500 border border-red-500 text-white p-4 rounded-xl text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('password.update') }}" method="POST">
                @csrf

                {{-- Hidden fields: token + email are part of the signed URL --}}
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="space-y-4">

                    {{-- Email (pre-filled from URL, hidden from user but sent in POST) --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">
                            Email Address
                        </label>
                        <input name="email" type="email" value="{{ old('email', $email) }}" required readonly
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white/60 rounded-xl focus:outline-none sm:text-sm cursor-not-allowed">
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">
                            New Password
                        </label>
                        <input name="password" id="password" type="password" required minlength="8"
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                            placeholder="Minimum 8 characters">
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">
                            Confirm New Password
                        </label>
                        <input name="password_confirmation" id="password_confirmation" type="password" required
                            minlength="8"
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                            placeholder="Repeat your new password">
                        {{-- Live match indicator --}}
                        <p id="pw-match-msg" class="mt-1 ml-1 text-xs hidden"></p>
                    </div>

                </div>

                <button type="submit" id="reset-btn"
                    class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 focus:outline-none transition-all uppercase tracking-widest">
                    Reset Password
                </button>
            </form>

            {{-- Back link --}}
            <p class="text-center text-base text-gray-500">
                <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300">Back to Login</a>
            </p>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pw = document.getElementById('password');
            const pwConf = document.getElementById('password_confirmation');
            const msg = document.getElementById('pw-match-msg');
            const btn = document.getElementById('reset-btn');

            function checkMatch() {
                if (!pwConf.value) {
                    msg.classList.add('hidden');
                    return;
                }
                const match = pw.value === pwConf.value;
                msg.classList.remove('hidden', 'text-emerald-400', 'text-red-400');
                if (match) {
                    msg.textContent = '✓ Passwords match';
                    msg.classList.add('text-emerald-400');
                } else {
                    msg.textContent = '✗ Passwords do not match';
                    msg.classList.add('text-red-400');
                }
            }

            pw.addEventListener('input', checkMatch);
            pwConf.addEventListener('input', checkMatch);

            // Loading state on submit
            const form = btn.closest('form');
            form.addEventListener('submit', function() {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.innerHTML = `
                    <span class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                    Resetting...
                `;
            });
        });
    </script>
</x-layout>
