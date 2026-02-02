<?php

use App\Http\Controllers\Admin\AdminController;
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
use App\Http\Controllers\FileProcessorController;
use App\Http\Controllers\RiskToolController;
use App\Http\Controllers\DataImportController;
use App\Http\Controllers\ReportController;

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
Route::get('/location-intelligence/{state}/{year?}', [LocationController::class, 'getTotalIncident'])->name('locationIntelligence');
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


Route::get('/all-insights', [HomeNewController::class, 'allInsights'])->name('insights.index');
Route::get('/insight/{id}', [HomeNewController::class, 'showDataInsights'])->name('insight.show');
Route::get('/news', [SecurityHubController::class, 'index'])->name('news');;

Route::post('/api/calc-risk', [HomeNewController::class, 'calculateHomepageRisk'])->name('api.calc-risk');
Route::get('/download-security-report', [SecurityHubController::class, 'downloadReport'])->name('reports.download');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

// If you haven't defined the download route yet, here is a placeholder pointing to the same controller
Route::get('/reports/download/{id?}', [ReportController::class, 'download'])->name('reports.download');


// Keep the name the same so your form works automatically
Route::post('/api/risk-analysis', [HomeNewController::class, 'analyze'])->name('risk-tool.analyze');
// Allows both POST (from your form) and GET (if you want to test via URL parameters)
Route::match(['get', 'post'], '/download-risk-report', [HomeNewController::class, 'downloadReport'])->name('report.download');
// Admin Dashboard Route
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// In routes/web.php



// ... inside your 'admin' prefix or middleware group ...
Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin-access'])->group(function () {

    // Existing Dashboard Route
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // --- User Management ---
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    // Optional: Add edit/update if needed later

    // --- Insight Management ---
    Route::get('/insights', [AdminController::class, 'insights'])->name('insights.index');
    Route::get('/insights/{id}/edit', [AdminController::class, 'editInsight'])->name('insights.edit');
    Route::put('/insights/{id}', [AdminController::class, 'updateInsight'])->name('insights.update');
    Route::delete('/insights/{id}', [AdminController::class, 'destroyInsight'])->name('insights.destroy');
});

Route::get('/file-processor', [FileProcessorController::class, 'index'])
    ->name('file-processor.index');

Route::post('/file-processor/process', [FileProcessorController::class, 'process'])
    ->name('file-processor.process');



// Data Import Routes
Route::prefix('data')->name('data.')->group(function () {

    // Main upload page with history
    Route::get('/import', [DataImportController::class, 'index'])
        ->name('import.index');

    // Process file upload
    Route::post('/import', [DataImportController::class, 'import'])
        ->name('import.store');

    // View import details
    Route::get('/import/{id}', [DataImportController::class, 'show'])
        ->name('import.show');

    // Download failed rows
    Route::get('/import/{id}/failed-rows', [DataImportController::class, 'downloadFailedRows'])
        ->name('import.download-failed');

    // Export imported incidents
    Route::get('/import/{id}/export', [DataImportController::class, 'exportImportedIncidents'])
        ->name('import.export-incidents');
});
