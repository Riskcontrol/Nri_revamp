<x-layout title="Login | Nigeria Risk Index">
    <div class="min-h-screen bg-[#0E1B2C] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-[#1E2D3D] p-10 rounded-3xl border border-white/5 shadow-2xl">
            <div class="text-center">
                <h2 class="text-3xl font-semibold text-white">Welcome Back</h2>
                <p class="mt-2 text-sm text-gray-400">Sign in to access your intelligence dashboard</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-500 border border-red-500 text-white p-4 rounded-xl text-xs">
                    Invalid credentials. Please check your email and password.
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Email
                            Address</label>
                        <input name="email" type="email" value="{{ old('email') }}" required autofocus
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>
                    <div>
                        <label
                            class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Password</label>
                        <input name="password" type="password" required
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-white/10 rounded bg-[#131C27]">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-400">Remember me</label>
                    </div>
                </div>

                <button type="submit"
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('form');

            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    const submitBtn = loginForm.querySelector('button[type="submit"]');

                    // 1. Disable the button so it can't be clicked again
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

                    // 2. Change the text to give user feedback
                    submitBtn.innerHTML = `
                    <span class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                    SIGNING IN...
                `;
                });
            }
        });
    </script>
</x-layout>
