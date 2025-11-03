  <main class="min-h-screen py-6 sm:py-8 text-white" style="background-color: #0e3a3f;">

      <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <!-- Outer board -->
          <div class=" overflow-hidden">
              <!-- Top bar -->
              <div class="bg-tealBar/90 px-5 sm:px-8 py-4 flex items-center justify-between">
                  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Nigeria Risk Heat Map</h1>
                  <span class="text-xl font-semibold">Cross-Industry Intelligence</span>
              </div>

              <!-- KPIs -->
              <div class="px-5 sm:px-8 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                  <div>
                      <p class="text-white/80">Active Incidents</p>
                      <p class="text-3xl font-extrabold text-kpiGreen">1,247</p>
                  </div>
                  <div>
                      <p class="text-white/80">High Risk States</p>
                      <p class="text-3xl font-extrabold text-kpiGreen">15</p>
                  </div>
                  <div>
                      <p class="text-white/80">Cross-Industry Correlations</p>
                      <p class="text-2xl font-extrabold text-kpiGreen">Strong</p>
                  </div>
                  <div class="text-right lg:text-left">
                      <p class="text-white/80">Filter by Date Range:</p>
                      <p class="text-2xl font-extrabold text-kpiGreen">Last 30 Days</p>
                  </div>
              </div>

              <!-- Body grid -->
              <div class="px-5 sm:px-8 pb-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
                  <!-- Left Sidebar -->
                  <aside class="lg:col-span-1">
                      <h3 class="text-lg font-extrabold mb-4">Risk Layers</h3>
                      <ul class="space-y-3 text-white/90">
                          <li class="flex items-center gap-3">
                              <span class="h-5 w-5 grid place-items-center rounded-md bg-low/20 ring-1 ring-low/40">
                                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16A34A"
                                      stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                      <polyline points="20 6 9 17 4 12" />
                                  </svg>
                              </span> Oil & Gas
                          </li>
                          <li class="flex items-center gap-3">
                              <span class="h-5 w-5 grid place-items-center rounded-md bg-low/20 ring-1 ring-low/40">
                                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16A34A"
                                      stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                      <polyline points="20 6 9 17 4 12" />
                                  </svg>
                              </span> Banking & Finance
                          </li>
                          <li class="flex items-center gap-3">
                              <span class="h-5 w-5 grid place-items-center rounded-md bg-low/20 ring-1 ring-low/40">
                                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16A34A"
                                      stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                      <polyline points="20 6 9 17 4 12" />
                                  </svg>
                              </span> Manufacturing
                          </li>
                          <li class="flex items-center gap-3">
                              <span class="h-5 w-5 grid place-items-center rounded-md bg-low/20 ring-1 ring-low/40">
                                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16A34A"
                                      stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                      <polyline points="20 6 9 17 4 12" />
                                  </svg>
                              </span> Agriculture
                          </li>
                      </ul>

                      <div class="mt-8">
                          <h3 class="text-lg font-extrabold">Risk Level</h3>
                          <ul class="mt-4 space-y-3">
                              <li class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-critical"></span>
                                  Critical</li>
                              <li class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-high"></span>
                                  High</li>
                              <li class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-medium"></span>
                                  Medium</li>
                              <li class="flex items-center gap-3"><span class="h-3 w-3 rounded-full bg-low"></span> Low
                              </li>
                          </ul>
                      </div>
                  </aside>

                  <!-- Map -->
                  <!-- Map -->
                  <div class="lg:col-span-2">
                      <div class="p-4">
                          <div class="relative " style="height: 500px; width:500px;">
                              <canvas id="ngMap" class="absolute inset-0 w-full h-full block"></canvas>
                          </div>
                          <!-- Custom legend -->

                      </div>

                      <!-- Export row -->
                      <div class="mt-4 flex items-center justify-end gap-4">
                          <button id="exportBtn"
                              class="inline-flex items-center gap-2 rounded-md bg-white/10 px-4 py-2 ring-1 ring-white/20 hover:bg-white/15">
                              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff"
                                  stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                  <polyline points="7 10 12 15 17 10" />
                                  <line x1="12" y1="15" x2="12" y2="3" />
                              </svg>
                              Export Map
                          </button>
                      </div>
                  </div>

              </div>
          </div>
      </section>
  </main>
