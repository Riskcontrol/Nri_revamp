<x-layout title="Forgot Password | Nigeria Risk Index">
    <div class="min-h-screen bg-[#0E1B2C] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-[#1E2D3D] p-10 rounded-3xl border border-white/5 shadow-2xl">

            {{-- Header --}}
            <div class="text-center">
                <h2 class="text-3xl font-semibold text-white">Reset Password</h2>
                <p class="mt-2 text-sm text-gray-400">
                    Enter your email and we'll send you a reset link.
                </p>
            </div>

            {{-- Success message (shown after form is submitted) --}}
            @if (session('status'))
                <div
                    class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-sm text-center">
                    {{ session('status') }}
                </div>
            @endif

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

            {{-- Only show the form if we haven't sent a link yet --}}
            @if (!session('status'))
                <form class="mt-8 space-y-6" action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">
                                Email Address
                            </label>
                            <input name="email" type="email" value="{{ old('email') }}" required autofocus
                                class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                                placeholder="you@example.com">
                        </div>
                    </div>

                    <button type="submit" id="reset-btn"
                        class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 focus:outline-none transition-all uppercase tracking-widest">
                        Send Reset Link
                    </button>
                </form>
            @endif

            {{-- Back to login --}}
            <p class="text-center text-base text-gray-500">
                Remembered your password?
                <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300">Back to Login</a>
            </p>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action="{{ route('password.email') }}"]');
            if (form) {
                form.addEventListener('submit', function() {
                    const btn = form.querySelector('button[type="submit"]');
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                    btn.innerHTML = `
                        <span class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                        Sending...
                    `;
                });
            }
        });
    </script>
</x-layout>
