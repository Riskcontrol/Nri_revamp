<div id="authRequiredModal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-md rounded-xl bg-[#111f3a] border border-gray-700 shadow-2xl p-6">
        <h3 class="text-lg font-bold text-white">Action requires an account</h3>
        <p class="mt-2 text-sm text-gray-300">
            Please create an account or sign in to use this feature.
        </p>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button id="authModalClose" class="px-4 py-2 rounded-lg bg-gray-700 text-white text-sm hover:bg-gray-600">
                Not now
            </button>

            <a id="authModalRegisterBtn" href="{{ route('register') }}"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                Register / Login
            </a>
        </div>
    </div>
</div>
