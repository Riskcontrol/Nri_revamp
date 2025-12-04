<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeNewController;
use App\Http\Controllers\LocationIntelligenceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SecurityIntelligenceController;
use App\Http\Controllers\RiskMapController;
use App\Http\Controllers\AnalyticsController;



Route::get('/', [HomeNewController::class, 'getStateRiskReports'])
    ->name('home');
// Route::post('/searched-location-intelligence/{state?}', [LocationIntelligenceController::class, 'index'])->name('locationIntelligence');
Route::get('/location-intelligence/{state}', [LocationController::class, 'getTotalIncident'])->name('locationIntelligence');
Route::get('/get-state-data/{state}/{year}', [LocationController::class, 'getStateData']);
Route::get('/get-total-incidents-only/{state}/{year}', [LocationController::class, 'getTotalIncidentsOnly']);
// Route to get high-impact incident coordinates for the map
Route::get('/get-incident-locations/{state}/{year}', [LocationController::class, 'getIncidentLocations']);

Route::get('/security-intelligence', [SecurityIntelligenceController::class, 'getOverview'])
    ->name('securityIntelligence');
Route::get('/get-top-5-risks/{state}/{year}', [LocationController::class, 'getTop5Risks']);

// Add this with your other location routes
Route::get('/get-lga-incident-counts/{state}/{year}', [LocationController::class, 'getLgaIncidentCounts']);

Route::get('/risk-treemap-data', [SecurityIntelligenceController::class, 'getRiskData']);

Route::get('/security-intelligence/analysis', [SecurityIntelligenceController::class, 'getRiskIndexAnalysis'])->name('security.analysis');

Route::get('/risk-map-data', [ASecurityIntelligenceController::class, 'getMapData']);




Route::get('/risk-map', [RiskMapController::class, 'showMapPage'])
     ->name('risk-map.show'); // We name it 'risk-map.show'

// THIS IS YOUR API ROUTE (you should already have this)
Route::get('/api/risk-map-data', [RiskMapController::class, 'getMapData'])
     ->name('map.data');

Route::get('/api/risk-map-card-data', [RiskMapController::class, 'getMapCardData'])->name('map.cardData');

Route::prefix('analytics')->group(function () {

    // 1. The View (The Dashboard Page)
    // URL: domain.com/analytics/dashboard
    Route::get('/dashboard', [AnalyticsController::class, 'index'])->name('analytics.view');

    // 2. The Filter Options (JSON for Dropdowns)
    // URL: domain.com/analytics/options
    Route::get('/options', [AnalyticsController::class, 'getFilterOptions'])->name('analytics.options');

    // 3. The Chart Data (JSON for Visualizations)
    // URL: domain.com/analytics/data
    Route::get('/data', [AnalyticsController::class, 'getFilteredStats'])->name('analytics.data');

});
