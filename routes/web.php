<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeNewController;
use App\Http\Controllers\LocationIntelligenceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SecurityIntelligenceController;
use App\Http\Controllers\RiskMapController;
use App\Http\Controllers\RiskMapAnalyticsController;
use App\Http\Controllers\SecurityHubController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;


Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->name('register')
    ->middleware('guest');

Route::post('/register', [RegisterController::class, 'register'])
    ->middleware('guest');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', [HomeNewController::class, 'getStateRiskReports'])
    ->name('home');
Route::get('/location-intelligence/{state}', [LocationController::class, 'getTotalIncident'])->name('locationIntelligence');
Route::get('/get-state-data/{state}/{year}', [LocationController::class, 'getStateData']);
Route::get('/get-total-incidents-only/{state}/{year}', [LocationController::class, 'getTotalIncidentsOnly']);
Route::get('/get-incident-locations/{state}/{year}', [LocationController::class, 'getIncidentLocations']);

Route::get('/security-intelligence', [SecurityIntelligenceController::class, 'getOverview'])
    ->name('securityIntelligence');
Route::get('/get-top-5-risks/{state}/{year}', [LocationController::class, 'getTop5Risks']);

Route::get('/get-lga-incident-counts/{state}/{year}', [LocationController::class, 'getLgaIncidentCounts']);

Route::get('/risk-treemap-data', [SecurityIntelligenceController::class, 'getRiskData']);

Route::get('/security-intelligence/analysis', [SecurityIntelligenceController::class, 'getRiskIndexAnalysis'])->name('security.analysis');

Route::get('/risk-map-data', [SecurityIntelligenceController::class, 'getMapData']);

Route::get('/get-comparison-risk-counts', [LocationController::class, 'getComparisonRiskCounts']);




Route::get('/risk-map', [RiskMapController::class, 'showMapPage'])
     ->name('risk-map.show'); // We name it 'risk-map.show'

// THIS IS YOUR API ROUTE (you should already have this)
Route::get('/api/risk-map-data', [RiskMapController::class, 'getMapData'])
     ->name('map.data');

Route::get('/api/risk-map-card-data', [RiskMapController::class, 'getMapCardData'])->name('map.cardData');


Route::get('/risk-map-analytics', [RiskMapAnalyticsController::class, 'index'])->name('risk-map.analytics');
Route::get('/risk-map-analytics/data', [RiskMapAnalyticsController::class, 'getData']);

Route::get('/all-insights', [HomeNewController::class, 'allInsights'])->name('insights.index');
Route::get('/insight/{id}', [HomeNewController::class, 'showDataInsights'])->name('insight.show');
Route::get('/news', [SecurityHubController::class, 'index'])->name('news');;

Route::post('/api/calc-risk', [HomeNewController::class, 'calculateHomepageRisk'])->name('api.calc-risk');
Route::get('/download-security-report', [SecurityHubController::class, 'downloadReport'])->name('reports.download');
