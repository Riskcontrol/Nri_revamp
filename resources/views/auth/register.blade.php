<x-layout title="Request Intelligence Access">
    <div class="min-h-screen bg-[#0E1B2C] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-[#1E2D3D] p-10 rounded-3xl border border-white/5 shadow-2xl">
            <div class="text-center">
                <h2 class="text-3xl font-semibold text-white">Access the Hub</h2>
                <p class="mt-2 text-sm text-gray-400">Request a professional demo for your organization</p>
            </div>
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500 text-red-500 p-4 rounded-xl mb-6 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Full
                            Name</label>
                        <input name="name" type="text" required
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Work
                            Email</label>
                        <input name="email" type="email" required
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>
                    <div>
                        <label
                            class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Organization</label>
                        <input name="organization" type="text" placeholder="Company, NGO, or Agency" required
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>
                    <div>
                        <label
                            class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Password</label>
                        <input name="password" type="password" required
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">Confirm
                            Password</label>
                        <input name="password_confirmation" type="password" required
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>
                </div>

                <button type="submit"
                    class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-500 focus:outline-none transition-all uppercase tracking-widest">
                    Request Demo Access
                </button>
            </form>

            <p class="text-center text-xs text-gray-500">
                Already have access? <a href="/login" class="text-blue-400 hover:text-blue-300">Login</a>
            </p>
        </div>
    </div>
</x-layout>
