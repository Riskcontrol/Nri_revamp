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
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">
                            Email</label>
                        <input name="email" type="email" required
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">
                            Organization Type
                        </label>

                        <select name="organization" id="organization" required
                            class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                            <option value="" disabled selected>Select an option</option>

                            <option value="Individual">Individual</option>

                            <option value="Corporate/Private Sector">Corporate/Private Sector</option>
                            <option value="International NGO">International NGO</option>
                            <option value="Local NGO">Local NGO</option>
                            <option value="Government Agency">Government Agency</option>
                            <option value="Media/Journalism">Media/Journalism</option>
                            <option value="Academic/Research">Academic/Research</option>
                            <option value="Consulting Firm">Consulting Firm</option>
                            <option value="Financial Institution">Financial Institution</option>
                            <option value="Other">Other</option>
                        </select>

                        {{-- Only show when "Other" is selected --}}
                        <div id="org_other_wrap" class="mt-3 hidden">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest ml-1">
                                Specify (Other)
                            </label>
                            <input name="organization_other" id="organization_other" type="text"
                                placeholder="Type your organization"
                                class="appearance-none relative block w-full px-4 py-4 border border-white/10 bg-[#131C27] text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                        </div>
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

            <p class="text-center text-base text-gray-500">
                Already have access? <a href="/login" class="text-blue-400 hover:text-blue-300">Login</a>
            </p>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orgSelect = document.getElementById('organization');
            const otherWrap = document.getElementById('org_other_wrap');
            const otherInput = document.getElementById('organization_other');
            const registerForm = document.querySelector('form[action="{{ route('register') }}"]');

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

            if (registerForm) {
                registerForm.addEventListener('submit', function() {
                    const submitBtn = registerForm.querySelector('button[type="submit"]');

                    // 1. Disable the button
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

                    // 2. Change text to "Requesting Access..." with a spinner
                    submitBtn.innerHTML = `
                    <span class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                    REQUESTING ACCESS...
                `;
                });
            }
        });
    </script>
</x-layout>
