<x-layout title="Location Intelligence"
    description="Welcome to the Nigeria Risk Index – your premier source for comprehensive security and risk
    analysis in Nigeria. Access up-to-date insights on terrorism, crime rates, and safety across Nigeria’s regions. Leverage
    our expert intelligence for businesses, expatriates, and travelers to make informed decisions and enhance safety.">
    <div class="container mx-auto p-6">
        <!-- Main Header with state name -->
        <h1 class="text-center text-3xl font-bold text-black mb-8">
            Location Intelligence for <span id="state-name">{{ $state }}</span> in <span
                id="current-year">{{ $year }}</span>
        </h1>

        <div class="flex justify-center items-center space-x-4 text-center mb-6">
            <div>
                <label for="state-select" class="text-lg font-medium text-gray-700">Select State:</label>
                <select id="state-select" class="py-2 px-4 border border-gray-300 rounded-md">
                    @foreach ($getStates as $s)
                        {{-- Set the current state as selected --}}
                        <option value="{{ $s }}" {{ $s == $state ? 'selected' : '' }}>{{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- NEW YEAR FILTER DROPDOWN --}}
            <div>
                <label for="year-select" class="text-lg font-medium text-gray-700">Select Year:</label>
                <select id="year-select" class="py-2 px-4 border border-gray-300 rounded-md">
                    @foreach ($availableYears as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Cards Section: Total Incidents, Risk Indicator, Affected LGA -->
        <div class="flex justify-between space-x-4 mb-8">
            <!-- Card 1: Total Incidents -->
            <div id="total-incidents" class="bg-white p-6 rounded-lg shadow-lg w-1/3 text-center">
                <h3 id="total-incidents-title" class="text-xl font-semibold text-[#185451] mb-3">Total Incidents
                    ({{ $year }})</h3>
                <p class="text-2xl font-medium text-gray-800 mt-2">{{ $total_incidents }}</p>
            </div>

            <!-- Card 2: Most Prevalent Risk Indicator -->
            <div id="most-frequent-risk" class="bg-white p-6 rounded-lg shadow-lg w-1/3 text-center">
                <h3 class="text-xl font-semibold text-[#185451] mb-3">Most Prevalent Risk</h3>
                <div id="most-frequent-risk-content" class="text-lg text-gray-700 mt-2">
                    <p>{{ $mostFrequentRisk->pluck('riskindicators')->implode(', ') ?: 'No data available' }}</p>
                </div>
            </div>

            <!-- Card 3: Most Affected LGA -->
            <div id="most-affected-lga" class="bg-white p-6 rounded-lg shadow-lg w-1/3 text-center">
                <h3 class="text-xl font-semibold text-[#185451] mb-3">Most Affected LGA</h3>
                <p class="text-lg text-gray-700 mt-2">
                    @if ($mostAffectedLGA)
                        {{ $mostAffectedLGA->lga }}
                        {{-- ({{ $mostAffectedLGA->occurrences }} incidents) --}}
                    @else
                        No data available
                    @endif
                </p>
            </div>
        </div>

        <!-- Charts Section: Side-by-Side for Incident Table and Motive Chart -->
        <div class="flex justify-between space-x-6 mb-8">
            <!-- Chart 1: Bar Chart for Incidents Over the Last 12 Months -->
            <div class="w-1/2 bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-center text-xl font-semibold text-[#185451] mb-4">Incidents Over the Past 12 Months</h3>
                <canvas id="myChart"></canvas>
            </div>

            <!-- Chart 2: Horizontal Bar Chart for Prevalent Risk Indicators -->
            <div class="w-1/2 bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-center text-xl font-semibold text-[#185451] mb-4">Prevalent Risk</h3>
                <canvas id="myChart2"></canvas>
            </div>
        </div>

        <!-- Charts Section: Side-by-Side for Yearly Incidents and Motive Types -->
        <div class="flex justify-between space-x-6 mb-8">

            <div class="w-1/2 bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-center text-xl font-semibold text-[#185451] mb-4" id="map-title">
                    High Impact Incidents in {{ $state }} ({{ $year }})
                </h3>
                <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
            </div>

            <!-- Motive Types by Occurrence Bar Chart -->
            <div class="w-1/2 bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-center text-xl font-semibold text-[#185451] mb-4">Incidents by Motive</h3>
                <canvas id="motiveChart"></canvas>
            </div>
        </div>

        <!-- Recent Incidents Table -->
        <div class="bg-white mb-8" id="recent-incidents">
            <h3 class="text-xl font-semibold text-[#185451] mb-4">Most Recent Incidents </h3>

            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">LGA</th>
                        <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">Incident</th>
                        <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">Risk </th>
                        <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">Impact</th>
                        <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">Date</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600">
                    @foreach ($recentIncidents as $incident)
                        <tr class="border-b border-gray-200">
                            <td class="py-2 px-4 border-r border-gray-200">{{ $incident->lga }}</td>
                            <td class="py-2 px-4 border-r border-gray-200">{{ $incident->add_notes }}</td>
                            <td class="py-2 px-4 border-r border-gray-200">{{ $incident->riskindicators }}</td>
                            <td class="py-2 px-4 border-r border-gray-200">{{ $incident->impact }}</td>
                            <td class="py-2 px-4">{{ \Carbon\Carbon::parse($incident->datecreated)->format('M d, Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <hr class="my-10 border-gray-300">

        <div class="mb-8">

            <div class="text-center mb-4">
                <label for="compare-state-select" class="text-lg font-medium text-gray-700">Compare Total Incidents
                    With:</label>
                <select id="compare-state-select" class="py-2 px-4 border border-gray-300 rounded-md">
                    <option value="" disabled selected>Select a State</option>
                    @foreach ($getStates as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-center mb-8">
                <div class="w-full bg-white p-6 rounded-lg shadow-md **max-w-lg**">
                    <h3 class="text-center text-xl font-semibold text-[#185451] mb-4" id="comparison-chart-title">
                        Risk Indicator Comparison
                    </h3>
                    <div class="**h-80**">
                        <canvas id="incidentCompareChart"></canvas>
                    </div>
                </div>
            </div>
        </div>




    </div>





    {{-- Chart.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>




    <script>
        // 1. Global Variables for all five charts
        let myChart, myChart2, motiveChart, incidentCompareChart;
        let map;
        // let markerClusterGroup;
        let geojsonLayer; // <-- NEW: This will hold our LGA shapes
        let lgaGeoJsonData; // <-- NEW: This will store the loaded JSON file
        let lgaIncidentData = {}; // <-- NEW: This will store the incident counts
        let info; // <-- NEW: This will be our hover info-box


        const formatDate = (dateString) => {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return dateString;
            }
            const options = {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            };
            // Format to "Oct 23 2025"
            return date.toLocaleDateString('en-US', options).replace(/,/, '');
        };

        // 2. Function to Initialize all Charts on Page Load
        async function initializeCharts() {
            // --- Existing Chart Initializations (Ensure they assign to the global variables) ---
            try {
                const response = await fetch('/nigeria-lga.json'); // Assumes file is in /public
                lgaGeoJsonData = await response.json();
                console.log("GeoJSON loaded successfully.");
            } catch (e) {
                console.error("CRITICAL: Could not load nigeria-lga.json.", e);
                // If the map shapes fail to load, we can't continue.
                document.getElementById('map-title').textContent =
                    "Map failed to load. Could not find nigeria-lga.json";
                return;
            }
            // Chart 1: Incidents Over Past Months (myChart)
            const ctx1 = document.getElementById('myChart').getContext('2d');
            const labels1 = @json($chartLabels);
            const dataValues1 = @json($incidentCounts);
            myChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: labels1,
                    datasets: [{
                        label: 'Incidents occurred in a month',
                        data: dataValues1,
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 4,

                        borderColor: 'rgba(27, 158, 133, 1)', // Solid purple line
                        backgroundColor: 'rgba(27, 158, 133, 0.2)', // Light purple fill
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Chart 2: Prevalent Risk Indicators (myChart2)
            const ctx2 = document.getElementById('myChart2').getContext('2d');
            const labels2 = @json($topRiskLabels);
            const dataValues2 = @json($topRiskCounts);
            myChart2 = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: labels2,
                    datasets: [{
                        label: 'Top 5 Risk Indicators',
                        data: dataValues2,
                        backgroundColor: [
                            'rgba(27, 158, 133, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });


            // Chart 4: Incidents by Motive (motiveChart)
            const motiveCtx = document.getElementById('motiveChart').getContext('2d');
            const motiveLabels = @json($motiveLabels);
            const motiveCounts = @json($motiveCounts);
            motiveChart = new Chart(motiveCtx, {
                type: 'pie',
                data: {
                    labels: motiveLabels,
                    datasets: [{
                        label: 'Motive Occurrences',
                        data: motiveCounts,
                        backgroundColor: [
                            'rgba(27, 158, 133, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    aspectRatio: 2,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });


            // --- NEW: Comparison Chart Initialization (incidentCompareChart) ---
            const compareCtx = document.getElementById('incidentCompareChart').getContext('2d');
            const defaultState = document.getElementById('state-name').textContent || 'Primary State';
            const defaultYear = document.getElementById('current-year').textContent;

            // Get the initial top 5 risk data (which is already loaded for myChart2)
            const initialRiskLabels = @json($topRiskLabels);
            const initialRiskCounts = @json($topRiskCounts);

            // Create an empty array of zeros for the comparison data
            const initialCompareCounts = new Array(initialRiskCounts.length).fill(0);

            incidentCompareChart = new Chart(compareCtx, {
                type: 'bar', // This is already 'bar', which is correct
                data: {
                    labels: initialRiskLabels, // Use Risk Labels for the X-axis
                    datasets: [
                        // Primary State Data
                        {
                            label: defaultState,
                            data: initialRiskCounts, // Use Risk Counts
                            backgroundColor: 'rgba(27, 158, 133, 0.7)',
                        },
                        // Comparison State Data (Starts empty)
                        {
                            label: 'Comparison State',
                            data: initialCompareCounts, // Use empty array
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Incident Count'
                            }
                        },
                        x: {
                            ticks: {
                                display: true // <-- IMPORTANT: We NOW want to see the x-axis labels
                            }
                        }
                    },
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });


            // Set the initial comparison title
            document.getElementById('comparison-chart-title').textContent =
                `Top 5 Risk Indicators(${defaultYear}): ${defaultState} vs None`;

            // --- NEW: Initialize the Map ---
            map = L.map('map').setView([9.0820, 8.6753], 6); // Center on Nigeria

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Initialize the GeoJSON layer (it's empty for now)
            // We add styling and interactivity logic here
            geojsonLayer = L.geoJson(null, {
                style: styleFeature,
                onEachFeature: onEachFeature
            }).addTo(map);

            // --- NEW: Add info control (hover box) ---
            info = L.control();
            info.onAdd = function(map) {
                this._div = L.DomUtil.create('div', 'info'); // create a div with a class "info"
                this.update();
                return this._div;
            };
            info.update = function(props) {
                // Get the count from our stored data
                const count = props ? (lgaIncidentData[props.NAME_2] || 0) : null;

                this._div.innerHTML = '<h4>Incident Count by LGA</h4>' + (props ?
                    '<b>' + props.NAME_2 + '</b><br />' + (count !== null ? count : 0) + ' Incidents' :
                    'Hover over an LGA');
            };
            info.addTo(map);

            // --- NEW: Add a legend ---
            const legend = L.control({
                position: 'bottomright'
            });
            legend.onAdd = function(map) {
                const div = L.DomUtil.create('div', 'info legend');
                const grades = [0, 10, 20, 50, 100, 200, 500]; // Our color steps
                let labels = [];

                // loop through our density intervals and generate a label with a colored square for each interval
                for (let i = 0; i < grades.length; i++) {
                    div.innerHTML +=
                        '<i style="background:' + getColor(grades[i] + 1) + '"></i> ' +
                        grades[i] + (grades[i + 1] ? '&ndash;' + grades[i + 1] + '<br>' : '+');
                }
                return div;
            };
            legend.addTo(map);


            updateMap(defaultState, defaultYear);
        }

        // 3. Function to update the Comparison Chart
        function updateComparisonChart() {
            const primaryState = document.getElementById('state-name').textContent;
            const compareState = document.getElementById('compare-state-select').value;
            const selectedYear = document.getElementById('year-select').value;
            // const primaryIncidents = parseInt(document.getElementById('total-incidents').querySelector('p').textContent) ||
            //     0;

            const primaryLabels = myChart2.data.labels;
            const primaryCounts = myChart2.data.datasets[0].data;

            // Update Primary State dataset label and data
            incidentCompareChart.data.datasets[0].label = primaryState;
            incidentCompareChart.data.datasets[0].data = primaryCounts;

            // Update Chart Title
            const titleElement = document.getElementById('comparison-chart-title');
            titleElement.textContent =
                `Top 5 Risk Indicators (${selectedYear}): ${primaryState} vs ${compareState || 'None'}`;

            if (compareState && compareState !== primaryState) {
                fetch(`/get-top-5-risks/${compareState}/${selectedYear}`)
                    .then(response => response.json())
                    .then(compareData => {

                        const alignedCompareCounts = primaryLabels.map(primaryLabel => {
                            // Find the index of this label in the comparison data
                            const indexInCompare = compareData.labels.indexOf(primaryLabel);

                            if (indexInCompare !== -1) {
                                // If found, use its count
                                return compareData.counts[indexInCompare];
                            } else {
                                // If not found, this risk isn't in the comparison's top 5, so count is 0
                                return 0;
                            }
                        });

                        incidentCompareChart.data.datasets[1].label = compareState;
                        incidentCompareChart.data.datasets[1].data = alignedCompareCounts;
                        incidentCompareChart.update();
                    })
                    .catch(error => console.error('Error fetching comparison data:', error));
            } else {
                // Clear comparison data if state is cleared or is the same as primary
                incidentCompareChart.data.datasets[1].label = 'Comparison State';
                // Create an array of zeros matching the length of the primary labels
                incidentCompareChart.data.datasets[1].data = new Array(primaryLabels.length).fill(0);
                incidentCompareChart.update();
            }
        }

        // --- 4. NEW: Function to Update the Map ---
        async function updateMap(state, year) {
            // Update map title
            document.getElementById('map-title').textContent = `Incident Density in ${state} (${year})`;

            // Step 1: Fetch your incident location data
            try {
                const response = await fetch(`/get-lga-incident-counts/${state}/${year}`);
                lgaIncidentData = await response.json(); // Store it globally
                console.log("LGA counts loaded:", lgaIncidentData);
            } catch (e) {
                console.error("Could not fetch LGA incident data", e);
                lgaIncidentData = {}; // Clear old data on failure
            }

            // Step 2: Clear old markers
            const stateFeatures = lgaGeoJsonData.features.filter(feature =>
                feature.properties.NAME_1 === state
            );

            if (stateFeatures.length === 0) {
                console.warn(`No GeoJSON features found for state: ${state}. Check 'state_name' property.`);
                geojsonLayer.clearLayers(); // Clear the map
                map.setView([9.0820, 8.6753], 6); // Re-center on Nigeria
                return;
            }

            const stateGeoJson = {
                type: "FeatureCollection",
                features: stateFeatures
            };
            // Step 3: Clear the old shapes and add the new ones
            geojsonLayer.clearLayers();
            geojsonLayer.addData(stateGeoJson);

            // Step 4: Zoom the map to fit the new state shapes
            map.fitBounds(geojsonLayer.getBounds(), {
                padding: [20, 20]
            });
        }


        // --- 5. NEW: Helper function for map coloring ---
        function getColor(count) {
            // These colors are from colorbrewer2.org (YlOrRd)
            return count > 500 ? '#800026' :
                count > 200 ? '#BD0026' :
                count > 100 ? '#E31A1C' :
                count > 50 ? '#FC4E2A' :
                count > 20 ? '#FD8D3C' :
                count > 10 ? '#FEB24C' :
                count > 0 ? '#FED976' :
                '#FFEDA0'; // Default for 0 or no data
        }

        // --- 6. NEW: Helper function for styling each LGA ---
        function styleFeature(feature) {
            // !!! IMPORTANT: CHECK YOUR GEOJSON PROPERTIES !!!
            // I am ASSUMING your GeoJSON has a property 'lga_name'
            // Open nigeria-lga.json to verify. It might be 'LGA_NAME', 'lga', etc.
            const lgaName = feature.properties.NAME_2;
            const count = lgaIncidentData[lgaName] || 0; // Get count from our fetched data

            return {
                fillColor: getColor(count),
                weight: 2,
                opacity: 1,
                color: 'white',
                dashArray: '3',
                fillOpacity: 0.7
            };
        }

        // --- 7. NEW: Helper function for interactivity ---
        function onEachFeature(feature, layer) {
            // Highlight feature on hover
            layer.on({
                mouseover: function(e) {
                    const layer = e.target;
                    layer.setStyle({
                        weight: 4,
                        color: '#666',
                        dashArray: '',
                        fillOpacity: 0.9
                    });
                    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                        layer.bringToFront();
                    }
                    info.update(layer.feature.properties); // Update info box
                },
                mouseout: function(e) {
                    geojsonLayer.resetStyle(e.target); // Reset to default style
                    info.update(); // Clear info box
                },
                click: function(e) {
                    map.fitBounds(e.target.getBounds()); // Zoom to feature on click
                }
            });
        }

        function updateMainDashboard(primaryState, selectedYear) {
            if (!primaryState || !selectedYear) return;

            // --- NEW: Update the map ---
            updateMap(primaryState, selectedYear);

            fetch(`/get-state-data/${primaryState}/${selectedYear}`)
                .then(response => response.json())
                .then(data => {
                    // Update CARDS
                    document.getElementById('total-incidents').querySelector('p').textContent = data.total_incidents;
                    document.getElementById('total-incidents-title').textContent = `Total Incidents (${selectedYear})`;

                    const riskContent = document.getElementById('most-frequent-risk-content');
                    let riskText = 'No data available'; // Default text

                    if (data.mostFrequentRisk && data.mostFrequentRisk.length > 0) {
                        // Map the array of objects to an array of names, then join with a comma
                        riskText = data.mostFrequentRisk.map(risk => risk.riskindicators).join(', ');
                    }

                    // Set the text content inside a single <p> tag
                    riskContent.innerHTML = `<p>${riskText}</p>`;
                    document.getElementById('most-affected-lga').querySelector('p').textContent = data.mostAffectedLGA ?
                        // data.mostAffectedLGA.lga + ' (' + data.mostAffectedLGA.occurrences + ' incidents)' : 'None';
                        data.mostAffectedLGA.lga : 'None';

                    // Update TABLE
                    let tableHTML = `
                    <h3 class="text-xl font-semibold text-[#185451] mb-4">Most Recent Incidents</h3>
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">LGA</th>
                                <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">Incident</th>
                                <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">Risk</th>
                                <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">Impact</th>
                                <th class="text-left py-3 px-4 font-semibold border-r border-gray-200">Date</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600">
                `;
                    data.recentIncidents.forEach(incident => {
                        tableHTML += `
                        <tr class="border-b border-gray-200">
                            <td class="py-2 px-4 border-r border-gray-200">${incident.lga}</td>
                            <td class="py-2 px-4 border-r border-gray-200">${incident.add_notes}</td>
                            <td class="py-2 px-4 border-r border-gray-200">${incident.riskindicators}</td>
                            <td class="py-2 px-4 border-r border-gray-200">${incident.impact}</td>
                            <td class="py-2 px-4">${formatDate(incident.datecreated)}</td>
                        </tr>
                    `;
                    });
                    tableHTML += '</tbody></table>';
                    document.getElementById('recent-incidents').innerHTML = tableHTML;

                    // Update the four main CHARTS
                    myChart.data.labels = data.chartLabels;
                    myChart.data.datasets[0].data = data.incidentCounts;
                    myChart.update();

                    myChart2.data.labels = data.topRiskLabels;
                    myChart2.data.datasets[0].data = data.topRiskCounts;
                    myChart2.update();

                    motiveChart.data.labels = data.motiveLabels;
                    motiveChart.data.datasets[0].data = data.motiveCounts;
                    motiveChart.update();

                    // CRITICAL STEP: After the main dashboard updates, update the comparison chart
                    updateComparisonChart();
                })
                .catch(error => console.error('Error fetching primary data:', error));
        }


        // 5. Event Listeners

        document.addEventListener('DOMContentLoaded', initializeCharts);

        function handleFilterChange() {
            const primaryState = document.getElementById('state-select').value;
            const selectedYear = document.getElementById('year-select').value; // <-- Get year

            // Update page titles
            document.getElementById('state-name').textContent = primaryState;
            document.getElementById('current-year').textContent = selectedYear;

            // Call update function with both values
            updateMainDashboard(primaryState, selectedYear);
        }

        // Attach the same handler to both dropdowns
        document.getElementById('state-select').addEventListener('change', handleFilterChange);
        document.getElementById('year-select').addEventListener('change', handleFilterChange); // <-- NEW LISTENER

        // Comparison State Listener (This remains the same)
        document.getElementById('compare-state-select').addEventListener('change', function() {
            updateComparisonChart();
        });
    </script>

</x-layout>
