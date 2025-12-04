<div class="max-w-7xl mx-auto mt-8 p-6 rounded-lg shadow-xl bg-[#1E2D3D]">
    <div class="space-y-2 mb-6">
        <h1 class="text-3xl font-bold text-white">Comprehensive Security Incidents Database (2018-2025)</h1>
        <p class="text-sm font-medium text-gray-300">Detailed incident records with advanced filtering and export
            capabilities</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6 items-end">
        <div>
            <label for="date-range-from" class="block text-sm font-medium text-gray-300">Date Range</label>
            <div class="flex items-center mt-1">
                <select id="date-range-from"
                    class="block w-full rounded-md border border-gray-600 bg-[#2b3a4a] py-2 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                    <option>2018</option>
                    <option selected>2018</option>
                    <option>2019</option>
                    <option>2020</option>
                    <option>2021</option>
                    <option>2022</option>
                    <option>2023</option>
                    <option>2024</option>
                    <option>2025</option>
                </select>
                <span class="mx-2 text-gray-300">to</span>
                <select id="date-range-to"
                    class="block w-full rounded-md border border-gray-600 bg-[#2b3a4a] py-2 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                    <option>2025</option>
                    <option>2018</option>
                    <option>2019</option>
                    <option>2020</option>
                    <option>2021</option>
                    <option>2022</option>
                    <option>2023</option>
                    <option>2024</option>
                    <option selected>2025</option>
                </select>
            </div>
        </div>

        <div>
            <label for="lga-filter" class="block text-sm font-medium text-gray-300">LGA</label>
            <select id="lga-filter"
                class="mt-1 block w-full rounded-md border border-gray-600 bg-[#2b3a4a] py-2 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                <option>All States</option>
                <option selected>All States</option>
                <option>Borno</option>
                <option>Kaduna</option>
                <option>Zamfara</option>
            </select>
        </div>

        <div>
            <label for="all-lgas-filter" class="block text-sm font-medium text-gray-300">All LGAs</label>
            <select id="all-lgas-filter"
                class="mt-1 block w-full rounded-md border border-gray-600 bg-[#2b3a4a] py-2 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                <option selected>All LGAs</option>
                <option>Gwoza</option>
                <option>Zangon Kataf</option>
                <option>Bungudu</option>
            </select>
        </div>

        <div>
            <label for="any-leevs-filter" class="block text-sm font-medium text-gray-300">Any Leevs</label>
            <select id="any-leevs-filter"
                class="mt-1 block w-full rounded-md border border-gray-600 bg-[#2b3a4a] py-2 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                <option selected>Any Leevs</option>
                <option>Option 1</option>
                <option>Option 2</option>
            </select>
        </div>

        <div class="lg:col-start-6">
            <button type="button"
                class="mt-1 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Apply Filters
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6 items-end">
        <div>
            <label for="incident-type-filter" class="block text-sm font-medium text-gray-300">Incident Type</label>
            <select id="incident-type-filter"
                class="mt-1 block w-full rounded-md border border-gray-600 bg-[#2b3a4a] py-2 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                <option selected>All Types</option>
                <option>Terrorism</option>
                <option>Abduction</option>
                <option>Armed Clash</option>
                <option>Armed Ramming</option>
                <option>Communal Violence</option>
            </select>
        </div>

        <div>
            <label for="risk-level-filter" class="block text-sm font-medium text-gray-300">Risk Level</label>
            <select id="risk-level-filter"
                class="mt-1 block w-full rounded-md border border-gray-600 bg-[#2b3a4a] py-2 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                <option selected>Risk Level</option>
                <option>Low</option>
                <option>Medium</option>
                <option>High</option>
                <option>Critical</option>
            </select>
        </div>

        <div>
            <label for="fatalities-filter" class="block text-sm font-medium text-gray-300">Fatalities</label>
            <select id="fatalities-filter"
                class="mt-1 block w-full rounded-md border border-gray-600 bg-[#2b3a4a] py-2 pl-3 pr-10 text-base text-white shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                <option selected>Any</option>
                <option>&gt; 0</option>
                <option>&gt; 10</option>
                <option>&gt; 50</option>
            </select>
        </div>

        <div>
            <button type="button"
                class="mt-1 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                Export to Excel
            </button>
        </div>

        <div>
            <button type="button"
                class="mt-1 w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50">
                Generate Report
            </button>
        </div>
    </div>

    <div class="flex justify-between items-center text-gray-300 text-sm mb-4">
        <p>Showing 1-50 of 12,347 incidents</p>
        <div class="flex items-center space-x-2">
            <span>Showing 1-50 of 12,847 incidents</span>
            <button class="px-2 py-1 rounded bg-[#2b3a4a] text-white">&lt;</button>
            <button class="px-2 py-1 rounded bg-[#2b3a4a] text-white">1</button>
            <button class="px-2 py-1 rounded bg-[#2b3a4a] text-white">2</button>
            <button class="px-2 py-1 rounded bg-[#2b3a4a] text-white">&gt;</button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg shadow-lg">
        <table class="min-w-full divide-y divide-gray-700 bg-[#2b3a4a]">
            <thead class="bg-[#1E2D3D]">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">State
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">LGA</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Incident
                        Type</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Risk
                        Level</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Fatalities</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <tr class="hover:bg-[#3d4f61]">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">Mar. 11, 2025</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Oyo</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Ibadan Nor</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Abduction</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500">High</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">42</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Abducted at iba on-thuome Tueday</td>
                </tr>
                <tr class="hover:bg-[#3d4f61]">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">Apr. 12, 2025</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Borno</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Gwoza</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Terrorism</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-500">Medium</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white flex items-center">
                        ? <button class="ml-1 text-gray-400 hover:text-white" title="Ask for details">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a1 1 0 00-1 1v3H6a1 1 0 100 2h3v3a1 1 0 102 0v-3h3a1 1 0 100-2h-3V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">...ned clash at ibaborare Kaklana</td>
                </tr>
                <tr class="hover:bg-[#3d4f61]">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">Apr. 11, 2024</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Rivers</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Obio/Akpor</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Armed clash</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500">High</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">3</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Armed slaglowed at Augran Matara</td>
                </tr>
                <tr class="hover:bg-[#3d4f61]">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">May. 7, 2024</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Kaduna</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Zangon Kataf</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Armed ramting</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-500">Medium</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">4</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Armed sourch in innoman Miga</td>
                </tr>
                <tr class="hover:bg-[#3d4f61]">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">May. 5, 2024</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Niger</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Shiroro</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Armed clash</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500">High</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">7</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Armed alrch at e nalo ally, June 2
                    </td>
                </tr>
                <tr class="hover:bg-[#3d4f61]">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">Jun. 28, 2024</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Zamfara</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Bungudu</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Abduction</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-500">Medium</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">1</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Armed altoing at a Katskan</td>
                </tr>
                <tr class="hover:bg-[#3d4f61]">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">Jun. 11, 2024</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Katsina</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Karakewa</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Terrorism</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500">High</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">2</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Communal violence on Monday</td>
                </tr>
                <tr class="hover:bg-[#3d4f61]">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">May 12, 2024</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Katsina</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Kannara</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Armed clash</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-500">Medium</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">3</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Armed unland at nugrau Bagaya</td>
                </tr>
                <tr class="hover:bg-[#3d4f61]">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">Jun. 28, 2024</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Jumbaru</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Katskara</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Communal violen</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-500">Medium</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">1</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">Intrudck meuoed sltion Monday</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
