<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeNewController;
use App\Http\Controllers\LocationIntelligenceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SecurityIntelligenceController;



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
