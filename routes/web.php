<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DataImportController;
use App\Http\Controllers\Admin\IncidentsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeNewController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SecurityIntelligenceController;
use App\Http\Controllers\RiskMapController;
use App\Http\Controllers\SecurityHubController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\FileProcessorController;
use App\Http\Controllers\RiskToolController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EnterpriseAccessController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\Admin\AdminReportController;

// ─── Auth routes ─────────────────────────────────────────────────────────────

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])
    ->middleware(['guest', 'throttle:3,60']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware(['throttle:10,1']);
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request')->middleware('guest');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email')->middleware(['guest', 'throttle:5,60']);
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset')->middleware('guest');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.update')->middleware(['guest', 'throttle:5,60']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Public routes ────────────────────────────────────────────────────────────

Route::get('/', [HomeNewController::class, 'getStateRiskReports'])->name('home');
Route::get('/location-intelligence/{state}/{year?}', [LocationController::class, 'getTotalIncident'])
    ->name('locationIntelligence');
Route::get('/get-state-data/{state}/{year}', [LocationController::class, 'getStateData'])
    ->middleware('auth.interact');
Route::get('/get-total-incidents-only/{state}/{year}', [LocationController::class, 'getTotalIncidentsOnly']);
Route::get('/get-incident-locations/{state}/{year}', [LocationController::class, 'getIncidentLocations'])
    ->middleware('auth.interact');
Route::get('/security-intelligence', [SecurityIntelligenceController::class, 'getOverview'])
    ->name('securityIntelligence');
Route::get('/get-top-5-risks/{state}/{year}', [LocationController::class, 'getTop5Risks']);
Route::get('/get-lga-incident-counts/{state}/{year}', [LocationController::class, 'getLgaIncidentCounts'])
    ->middleware('auth.interact');
Route::get('/risk-preview-data', [SecurityIntelligenceController::class, 'getPreviewRiskData'])
    ->name('risk.preview');
Route::get('/risk-treemap-data', [SecurityIntelligenceController::class, 'getRiskData'])
    ->middleware('auth.interact');
Route::get('/security-intelligence/analysis', [SecurityIntelligenceController::class, 'getRiskIndexAnalysis'])
    ->name('security.analysis');
Route::get('/risk-map-data', [SecurityIntelligenceController::class, 'getMapData']);
Route::get('/get-comparison-risk-counts', [LocationController::class, 'getComparisonRiskCounts'])
    ->middleware('auth.interact');
Route::get('/enterprise-access', [EnterpriseAccessController::class, 'create'])
    ->name('enterprise-access.create');
Route::post('/enterprise-access', [EnterpriseAccessController::class, 'store'])
    ->name('enterprise-access.store')->middleware(['throttle:4,60']);
Route::get('/risk-map', [RiskMapController::class, 'showMapPage'])->name('risk-map.show');
Route::get('/api/risk-map-preview', [RiskMapController::class, 'getPreviewData']);
Route::get('/api/risk-map-preview-card', [RiskMapController::class, 'getPreviewCardData']);
Route::get('/api/risk-map-data', [RiskMapController::class, 'getMapData'])
    ->name('map.data')->middleware('auth.interact');
Route::get('/api/risk-map-card-data', [RiskMapController::class, 'getMapCardData'])
    ->name('map.cardData')->middleware('auth.interact');
Route::get('/all-insights', [HomeNewController::class, 'allInsights'])->name('insights.index');
Route::get('/insight/{id}', [HomeNewController::class, 'showDataInsights'])->name('insight.show');
Route::get('/news', [SecurityHubController::class, 'index'])->name('news');
Route::post('/api/calc-risk', [HomeNewController::class, 'calculateHomepageRisk'])->name('api.calc-risk');
Route::get('/download-security-report', [SecurityHubController::class, 'downloadReport'])
    ->name('security.report.download');
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/{report}/download', [ReportController::class, 'download'])
    ->name('reports.download')->middleware('auth.interact');
Route::post('/api/risk-analysis', [HomeNewController::class, 'analyze'])
    ->name('risk-tool.analyze')->middleware(['throttle:30,1']);
Route::match(['get', 'post'], '/download-risk-report', [HomeNewController::class, 'requestReport'])
    ->name('report.download');
Route::get('/security-alert/{eventid}', [SecurityHubController::class, 'showAlert'])
    ->name('security-alert.show');

// ─── Newsletter ───────────────────────────────────────────────────────────────

Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])
    ->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

// ─── Admin routes — ALL require auth + admin middleware ───────────────────────

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        // ── User management ───────────────────────────────────────────────
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/users/{user}/tier', [AdminController::class, 'updateUserTier'])
            ->name('users.update-tier');

        // ── Insight management ────────────────────────────────────────────
        Route::get('/insights', [AdminController::class, 'insights'])->name('insights.index');
        Route::get('/insights/{id}/edit', [AdminController::class, 'editInsight'])->name('insights.edit');
        Route::put('/insights/{id}', [AdminController::class, 'updateInsight'])->name('insights.update');
        Route::delete('/insights/{id}', [AdminController::class, 'destroyInsight'])->name('insights.destroy');

        // ── File Processor ────────────────────────────────────────────────
        Route::get('/file-processor', [FileProcessorController::class, 'index'])
            ->name('file-processor.index');
        Route::post('/file-processor/process', [FileProcessorController::class, 'process'])
            ->name('file-processor.process');

        // ── Data Import ───────────────────────────────────────────────────
        Route::prefix('data-import')->name('data-import.')->group(function () {
            Route::get('/', [DataImportController::class, 'index'])->name('index');
            Route::post('/', [DataImportController::class, 'import'])->name('store');
            Route::get('/{id}', [DataImportController::class, 'show'])->name('show');
            Route::delete('/{id}/data', [DataImportController::class, 'deleteData'])->name('delete-data');
            Route::delete('/{id}', [DataImportController::class, 'destroyImport'])->name('destroy');
            Route::delete('/{id}/incident', [DataImportController::class, 'deleteIncident'])
                ->name('delete-incident');
            Route::get('/{id}/failed-rows', [DataImportController::class, 'downloadFailedRows'])
                ->name('download-failed');
            Route::get('/{id}/export', [DataImportController::class, 'exportImportedIncidents'])
                ->name('export-incidents');
        });

        // ── All Incidents data table ──────────────────────────────────────
        // GET  /admin/incidents            → paginated listing with filters
        // DELETE /admin/incidents/row      → delete single row (JSON)
        // DELETE /admin/incidents/bulk     → delete multiple rows (JSON)
        // POST   /admin/incidents/breaking → toggle Breaking News flag (JSON)
        Route::prefix('incidents')->name('incidents.')->group(function () {
            Route::get('/', [IncidentsController::class, 'index'])->name('index');
            Route::delete('/row', [IncidentsController::class, 'destroy'])->name('destroy');
            Route::delete('/bulk', [IncidentsController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::post('/breaking', [IncidentsController::class, 'toggleBreakingNews'])
                ->name('toggle-breaking');
        });
        Route::post('/announcement',   [AdminController::class, 'updateAnnouncement'])->name('announcement.update');
        Route::delete('/announcement', [AdminController::class, 'deleteAnnouncement'])->name('announcement.delete');

        // ── Report management ─────────────────────────────────────────────────────
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/',                         [AdminReportController::class, 'index'])->name('index');
            Route::get('/create',                   [AdminReportController::class, 'create'])->name('create');
            Route::post('/',                        [AdminReportController::class, 'store'])->name('store');
            Route::get('/{report}/edit',            [AdminReportController::class, 'edit'])->name('edit');
            Route::put('/{report}',                 [AdminReportController::class, 'update'])->name('update');
            Route::post('/{report}/toggle-publish', [AdminReportController::class, 'togglePublish'])->name('toggle-publish');
            Route::delete('/{report}',              [AdminReportController::class, 'destroy'])->name('destroy');
        });
    });
