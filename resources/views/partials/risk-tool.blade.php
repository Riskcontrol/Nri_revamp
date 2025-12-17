<div class="py-10 md:py-14 bg-white">
    {{-- CHANGED: max-w-6xl -> max-w-7xl and added standard padding --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="shadow-2xl rounded-2xl overflow-hidden flex flex-col md:flex-row border border-white/5">

            {{-- Left Side: Calculator Form --}}
            <div class="w-full md:w-3/5 bg-[#1A2B3C] p-6 md:p-10 text-white">

                <p class="text-xs md:text-sm text-emerald-400 font-medium mb-1">
                    Nigeria Risk Assessment Calculator
                </p>
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2 uppercase">
                    INTERACTIVE SECURITY ANALYSIS TOOL
                </h1>
                <div
                    class="flex items-center text-base md:text-lg text-gray-300 font-light pb-6 border-b border-gray-700/50 mb-8">
                    Get Personalized Risk Insights
                    <svg class="w-5 h-5 ml-1 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">

                    <div>
                        <h3 class="text-sm md:text-md font-bold uppercase tracking-wider mb-2 flex items-center">
                            STEP 1. LOCATION
                            <svg class="w-4 h-4 ml-1 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-400 text-xs md:text-sm mb-1">Select State</label>
                                <select
                                    class="w-full p-2.5 bg-[#2A3F50] border border-gray-600 rounded text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                    <option>Lagos</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs md:text-sm mb-1">Select LGA</label>
                                <select
                                    class="w-full p-2.5 bg-[#2A3F50] border border-gray-600 rounded text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                    <option>Lagos Island</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm md:text-md font-bold uppercase tracking-wider mb-2">
                            STEP 3. ORGANIZATION
                        </h3>
                        <label class="block text-gray-400 text-xs md:text-sm mb-1">Select Business Type</label>
                        <div class="space-y-3 mt-1">
                            <button
                                class="w-full text-left py-2.5 px-3 bg-emerald-600 border border-emerald-600 rounded-md font-medium flex justify-between items-center transition-colors text-sm">
                                Oil & Gas
                                <span class="text-xl leading-none">&rsaquo;</span>
                            </button>
                            <button
                                class="w-full text-left py-2.5 px-3 bg-[#2A3F50] border border-gray-600 rounded-md font-medium flex justify-between items-center hover:bg-[#3A4F60] transition-colors text-sm">
                                Manufacturing
                                <span class="text-xl leading-none">&rsaquo;</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm md:text-md font-bold uppercase tracking-wider mb-2">
                            STEP 5. CURRENT SECURITY
                        </h3>
                        <div class="flex items-center space-x-2 mb-6">
                            <span class="text-gray-400 text-sm font-medium">Overall</span>
                            <span class="px-3 py-1 bg-red-700 text-white rounded text-sm font-bold">High ^</span>
                        </div>

                        <button
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md uppercase transition-colors text-sm md:text-base shadow-lg hover:shadow-emerald-900/50">
                            CALCULATE RISK SCORE
                        </button>
                    </div>

                    <div>
                        <h3 class="text-sm md:text-md font-bold uppercase tracking-wider mb-2">
                            STEP 4. OPERATIONS
                        </h3>
                        <ul class="space-y-2 text-sm text-gray-300">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-emerald-500 mr-2 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Security Personnel
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-emerald-500 mr-2 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                CCTV Systems
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-emerald-500 mr-2 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Access Controls
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-emerald-500 mr-2 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Emergency Protocols
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

            {{-- Right Side: Results --}}
            <div
                class="w-full md:w-2/5 bg-white p-6 md:p-10 text-gray-900 border-t md:border-t-0 md:border-l border-gray-200">

                <h2 class="text-lg font-bold uppercase tracking-wider text-gray-700 mb-6 text-center md:text-left">
                    YOUR RISK ASSESSMENT
                </h2>

                <div class="relative w-32 h-32 mx-auto mb-6">
                    <div class="w-full h-full rounded-full border-4 border-gray-200 absolute"></div>
                    <div class="w-full h-full rounded-full border-4 border-emerald-500/50 absolute clip-path-risk">
                    </div>
                    <div
                        class="absolute inset-2 bg-white rounded-full flex flex-col items-center justify-center border-4 border-white shadow-lg">
                        <p class="text-3xl md:text-4xl font-black text-gray-900">High</p>
                        <p class="text-xs text-gray-500">(Overall)</p>
                    </div>
                </div>

                <div class="space-y-3 mb-8 bg-gray-50 p-4 rounded-lg">
                    <p class="flex justify-between text-sm font-medium">
                        <span class="text-gray-600">Terrorism Risk:</span>
                        <span class="text-yellow-600 font-bold">Moderate</span>
                    </p>
                    <p class="flex justify-between text-sm font-medium">
                        <span class="text-gray-600">Kidnapping Risk:</span>
                        <span class="text-red-600 font-bold">High</span>
                    </p>
                    <p class="flex justify-between text-sm font-medium">
                        <span class="text-gray-600">Crime Risk:</span>
                        <span class="text-red-600 font-bold">High</span>
                    </p>
                </div>

                <h3
                    class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4 border-t pt-4 text-center md:text-left">
                    Register for Full Report
                </h3>

                <div class="space-y-3">
                    <button
                        class="w-full py-3 bg-[#2196F3] hover:bg-blue-600 text-white font-medium rounded-md shadow-md transition-colors text-sm">
                        View Detailed Analysis
                    </button>
                    <button
                        class="w-full py-3 bg-white border border-[#2196F3] text-[#2196F3] hover:bg-blue-50 font-medium rounded-md transition-colors text-sm">
                        Download Risk Report
                    </button>
                    {{-- Hidden secondary buttons for cleaner mobile view --}}
                    <div class="hidden sm:block space-y-3">
                        <button
                            class="w-full py-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium rounded-md transition-colors text-sm">
                            Get Mitigation Strategies
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .clip-path-risk {
        clip-path: polygon(0 0, 100% 0, 100% 80%, 0 80%);
    }
</style>
