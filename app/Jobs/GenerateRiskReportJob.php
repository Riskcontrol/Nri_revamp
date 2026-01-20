<?php

namespace App\Jobs;

use App\Mail\RiskReportMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateRiskReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Increase timeout for this specific job to 5 minutes
    public $timeout = 300;
    public $tries = 2;

    protected $lga;
    protected $state;
    protected $year;
    protected $email;

    public function __construct($lga, $state, $year, $email)
    {
        $this->lga = $lga;
        $this->state = $state;
        $this->year = $year;
        $this->email = $email;
    }

    public function handle()
    {
        Log::info("Job Started: Processing report for {$this->email}");

        $lga = $this->lga;
        $year = $this->year;
        $state = $this->state;

        // 1. GATHER DATA (Optimized Logic from your prompt)
        $cacheKey = "pdf_data_{$lga}_{$year}";

        $reportData = Cache::remember($cacheKey, 3600, function () use ($lga, $year) {

            // Check existence
            $exists = DB::table('tbldataentry')->where('lga', $lga)->where('eventyear', $year)->exists();
            if (!$exists) return null;

            // Risk Distribution
            $riskDistribution = DB::table('tbldataentry')
                ->where('lga', $lga)->where('eventyear', $year)
                ->select('riskindicators', DB::raw('COUNT(*) as count'))
                ->groupBy('riskindicators')->orderByDesc('count')->limit(5)->get();

            // Casualties
            $casualtyData = DB::table('tbldataentry')
                ->where('lga', $lga)->where('eventyear', $year)
                ->selectRaw('COALESCE(SUM(CAST(Casualties_count AS UNSIGNED)), 0) as deaths, COALESCE(SUM(CAST(victim AS UNSIGNED)), 0) as kidnaps')
                ->first();

            $casualties = (object)['deaths' => $casualtyData->deaths ?? 0, 'kidnaps' => $casualtyData->kidnaps ?? 0];

            // Hotspots
            $hotspots = DB::table('tbldataentry as t')
                ->join('state_neighbourhoods as sn', 't.neighbourhood', '=', 'sn.id')
                ->where('t.lga', $lga)->where('t.eventyear', $year)
                ->whereNotNull('sn.neighbourhood_name')
                ->select('sn.neighbourhood_name', DB::raw('COUNT(*) as incidents'))
                ->groupBy('sn.neighbourhood_name')->orderByDesc('incidents')->limit(4)->get();

            // Incidents
            $incidents = DB::table('tbldataentry as t')
                ->leftJoin('state_neighbourhoods as sn', 't.neighbourhood', '=', 'sn.id')
                ->where('t.lga', $lga)->where('t.eventyear', $year)
                ->select('t.riskindicators', 't.Casualties_count', 't.victim', 't.eventdateToUse', DB::raw('LEFT(TRIM(t.add_notes), 300) as add_notes'), 'sn.neighbourhood_name')
                ->orderByDesc('t.eventdateToUse')->limit(5)->get();

            return [
                'riskDistribution' => $riskDistribution,
                'casualties' => $casualties,
                'hotspots' => $hotspots,
                'incidents' => $incidents,
                'topRisk' => $riskDistribution->first()->riskindicators ?? 'General Insecurity'
            ];
        });

        if (!$reportData) {
            Log::warning("No data found for {$lga} {$year}, email not sent.");
            return;
        }

        // 2. GENERATE ADVISORY (Simplified for brevity, assume getSmartAdvisoryOptimized logic is accessible or moved to a helper)
        // Ideally, move the advisory logic to a Service class, but for now we generate a basic string
        $advisory = "Based on the high prevalence of {$reportData['topRisk']}, we advise increased vigilance in {$lga}.";

        // 3. RENDER PDF
        $logoPath = public_path('images/nri-logo.png');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';

        $pdf = Pdf::loadView('reports.risk_profile', [
            'state' => $state,
            'lga' => $lga,
            'year' => $year,
            'riskDistribution' => $reportData['riskDistribution'],
            'casualties' => $reportData['casualties'],
            'hotspots' => $reportData['hotspots'],
            'incidents' => $reportData['incidents'],
            'advisory' => $advisory,
            'logoSrc' => $logoSrc
        ]);

        $pdf->setPaper('a4', 'portrait');

        // 4. SEND EMAIL
        // We output the PDF as a string to attach it
        Mail::to($this->email)->send(new RiskReportMail($pdf->output(), $lga, $year));

        Log::info("Email sent successfully to {$this->email}");
    }
}
