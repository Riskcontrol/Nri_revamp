<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use Illuminate\Http\Request;
use App\Models\CorrectionFactorForStates;
// use Carbon\Carbon;
use App\Models\tblriskindicators;
use App\Models\DataInsights;
use App\Models\DataInsightsCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\CalculatesRisk;
// use PDF;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\ReportRecipient;
use App\Jobs\GenerateRiskReportJob;

class HomeNewController extends Controller
{
    use CalculatesRisk;


    /**
     * Main route for the Homepage Dashboard
     */
    public function getStateRiskReports(Request $request)
    {
        // =============================
        // STATIC TABLE DATA (NO DB)
        // =============================
        $securityIndexRows = $this->getStaticSecurityIndexRows();

        $year = (int) ($request->query('year') ?: now()->subYear()->year);

        $data = tbldataentry::selectRaw("
            TRIM(location) as location,
            TRIM(riskindicators) as risk_indicator,
            yy,
            COUNT(*) as total_incidents,
            COALESCE(SUM(Casualties_count),0) as total_deaths,
            COALESCE(SUM(victim),0) as total_victims
        ")
            ->where('yy', $year)
            ->groupBy(DB::raw('TRIM(location)'), DB::raw('TRIM(riskindicators)'), 'yy')
            ->get();

        $totalFatalities = (int) $data->sum('total_deaths');


        // ✅ This now works correctly
        $stateRiskReports = $this->calculateStateRiskFromIndicators($data);


        // Top 3 most affected (by deaths) - update to match renamed field
        $top3HighRiskStates = $data
            ->groupBy('location')
            ->map(function ($rows, $location) {
                $deaths = $rows->sum('total_deaths');
                return [
                    'location' => $location,
                    'human_toll' => $deaths
                ];
            })
            ->sortByDesc('human_toll')
            ->take(3)
            ->pluck('location')
            ->implode(', ');

        // 4. Trending Risk Factors (keep as-is)
        $trendingRiskFactors = tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency, SUM(victim) as total_victims, SUM(Casualties_count) as total_casualties')
            ->groupBy('riskindicators')
            ->orderByDesc('frequency')
            ->take(4)
            ->get();

        // 5. Recent incidents & Audited Time
        $now = Carbon::now();
        $recentIncidentsCount = tbldataentry::where('audittimecreated', '>=', $now->subDay())->count();
        $latestIncidentTime = tbldataentry::latest('audittimecreated')->first();
        $auditedTime = $latestIncidentTime ? Carbon::parse($latestIncidentTime->audittimecreated)->format('h:i:s A') : 'N/A';

        // 6. Total incidents for current scope (2025)
        $totalIncidents = tbldataentry::where('yy', $year)->count();

        // 7. Current Threat Level calculation
        $compositeIndexes = $this->calculateCompositeIndexByRiskFactors($year);

        // $values = array_values($compositeIndexes);
        // rsort($values); // descending

        // $avg = !empty($values) ? (array_sum($values) / count($values)) : 0;

        // $top5 = array_slice($values, 0, 5);
        // $top5Avg = !empty($top5) ? (array_sum($top5) / count($top5)) : 0;

        // // National pressure score (same ~0–10-ish scale your thresholds expect)
        // $nationalScore = ($top5Avg * 0.6) + ($avg * 0.4);

        // $compositeIndexes = $this->calculateCompositeIndexByRiskFactorsRaw(2025);
        // $nationalAverage = array_sum($compositeIndexes) / count($compositeIndexes);
        // $no =  count($compositeIndexes);
        // dd("$no $nationalAverage");
        $series = $this->getNationalThreatSeries(2018, now()->year);

        $current = $series[$year];

        $level = $this->classifyNationalThreat($current, $series);
        // dd($level );

        // $currentThreatLevel = match ($level) {
        //     1 => 'LOW',
        //     2 => 'MEDIUM',
        //     3 => 'HIGH',
        //     4 => 'VERY HIGH',
        //     default => 'LOW',
        // };
        $currentThreatLevel = $level;


        // 9. Fetch Insights
        $homeInsights = DataInsights::with('category')->latest()->take(4)->get();

        $states = collect(config('nigeria'));

        return view('home', compact(
            'securityIndexRows',
            'totalFatalities',
            'top3HighRiskStates',
            'trendingRiskFactors',
            'recentIncidentsCount',
            'auditedTime',
            'totalIncidents',
            'currentThreatLevel',
            'homeInsights',
            'states',
        ));
    }



    /**
     * Map and Violence Index Processing
     */


    /**
     * Individual Insight Detail Page
     */
    public function showDataInsights($id)
    {
        $post = is_numeric($id)
            ? DataInsights::findOrFail($id)
            : DataInsights::where('slug', $id)->orWhere('title', $id)->firstOrFail();

        $relatedCategoryPost = DataInsights::where('id', '!=', $post->id)->latest()->take(4)->get();
        $categories = DataInsightsCategory::orderBy('name', 'asc')->get();

        return view('showDataInsight', compact('post', 'relatedCategoryPost', 'categories'));
    }

    /**
     * All Insights Index Page
     */
    public function allInsights(Request $request)
    {
        $insights = DataInsights::with('category')->where('category_id', 2)->latest()->paginate(12);
        $categories = DataInsightsCategory::orderBy('name', 'asc')->get();
        return view('insights', compact('insights', 'categories'));
    }

    // --- Helper Methods Supporting map data ---

    private function getStaticSecurityIndexRows(): array
    {
        return [
            ['location' => 'Abia', 'incident_count' => 42, 'nti_score' => 1.52, 'risk_level' => 'Low'],
            ['location' => 'Adamawa', 'incident_count' => 46, 'nti_score' => 1.63, 'risk_level' => 'Low'],
            ['location' => 'Akwa Ibom', 'incident_count' => 35, 'nti_score' => 0.68, 'risk_level' => 'Low'],
            ['location' => 'Anambra', 'incident_count' => 111, 'nti_score' => 2.64, 'risk_level' => 'Low'],
            ['location' => 'Bauchi', 'incident_count' => 47, 'nti_score' => 1.41, 'risk_level' => 'Low'],
            ['location' => 'Bayelsa', 'incident_count' => 16, 'nti_score' => 0.51, 'risk_level' => 'Low'],
            ['location' => 'Benue', 'incident_count' => 196, 'nti_score' => 11.29, 'risk_level' => 'High'],
            ['location' => 'Borno', 'incident_count' => 139, 'nti_score' => 8.76, 'risk_level' => 'High'],
        ];
    }

    // Risk tool logic
    public function analyze(Request $request)
    {
        $lga = $request->input('lga');
        $state = $request->input('state');
        $year = $request->input('year', date('Y'));

        // 1. LGA Specific Stats
        $stats = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->where('eventyear', $year)
            ->selectRaw('count(*) as total, sum(COALESCE(CAST(Casualties_count AS UNSIGNED), 0)) as casualties')
            ->first();

        // 2. Trend Logic (Compared to previous year)
        $prevYearCount = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->where('eventyear', $year - 1)
            ->count();
        $trend = ($prevYearCount > 0) ? round((($stats->total - $prevYearCount) / $prevYearCount) * 100, 1) : 0;

        // 3. Top Risk Factor
        $topIndicator = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->where('eventyear', $year)
            ->select('riskindicators', DB::raw('count(*) as count'))
            ->groupBy('riskindicators')
            ->orderBy('count', 'desc')
            ->first();

        // 4. COMPARATIVE BENCHMARK (State Average)
        $cacheKey = "state_avg_{$state}_{$year}";
        $stateAvg = Cache::remember($cacheKey, 3600, function () use ($state, $year) {
            return DB::table('tbldataentry')
                ->where('location', $state)
                ->where('eventyear', $year)
                ->selectRaw('count(*) / count(distinct lga) as avg_val')
                ->value('avg_val') ?? 0;
        });

        // 5. Recent Incidents
        $recent = DB::table('tbldataentry')
            ->where('location', $state)
            ->orderBy('eventdateToUse', 'desc')
            ->limit(2)
            ->get(['eventdateToUse', 'add_notes as incident_description', 'lga']);

        $score = $this->calculateRiskScore($stats->total, $stats->casualties, $trend);

        return response()->json([
            'success' => true,
            'score' => $score,
            'total' => $stats->total,
            'casualties' => $stats->casualties ?? 0,
            'top_indicator' => $topIndicator->riskindicators ?? 'N/A',
            'impact_level' => $this->getImpactLabel($score),
            'state_avg' => round($stateAvg, 1),
            'comparison' => ($stats->total > $stateAvg) ? 'higher' : 'lower',
            'recent_incidents' => $recent
        ]);
    }

    private function getImpactLabel($score)
    {
        if ($score >= 75) return 'Critical';
        if ($score >= 50) return 'High';
        if ($score >= 25) return 'Medium';
        return 'Low';
    }
    private function calculateRiskScore($total, $casualties, $trend)
    {
        // Logic: Weighting incident volume (40%), lethality (40%), and trend (20%)
        $score = ($total * 1.5) + ($casualties * 3) + ($trend > 0 ? 10 : 0);
        return min(100, round($score));
    }



    public function downloadReport(Request $request)
    {
        // 1. Validate Input
        $validated = $request->validate([
            'state' => 'required|string',
            'lga' => 'required|string',
            'email' => 'required|email',
            'year' => 'nullable|integer|min:2018|max:' . date('Y')
        ]);

        $year = $validated['year'] ?? date('Y');

        // 2. Save or Update Recipient (Prevent Duplicates)
        // If email exists, update timestamp and increment count. If new, create it.
        $recipient = ReportRecipient::updateOrCreate(
            ['email' => $validated['email']], // Search condition
            [
                'last_state_requested' => $validated['state'],
                'last_lga_requested' => $validated['lga'],
                'last_request_at' => now(),
                'request_count' => DB::raw('request_count + 1') // SQL increment
            ]
        );

        // 3. Dispatch Background Job
        // This puts the task in the queue and returns immediately to the user
        GenerateRiskReportJob::dispatch(
            $validated['lga'],
            $validated['state'],
            $year,
            $validated['email']
        );

        // 4. Return Immediate JSON Response
        return response()->json([
            'success' => true,
            'message' => "Request received! We are generating the report for {$validated['lga']} and will email it to {$validated['email']} within a few minutes."
        ], 200);
    }

    // OPTIMIZED: Advisory generation with better caching strategy
    private function getSmartAdvisoryOptimized($lga, $state, $topRisk, $year)
    {
        $advisoryCacheKey = "advisory_{$lga}_{$year}";

        return Cache::remember($advisoryCacheKey, 7200, function () use ($lga, $state, $topRisk, $year) {

            // SINGLE optimized query for trend calculation
            $trendData = DB::table('tbldataentry')
                ->where('lga', $lga)
                ->whereIn('eventyear', [$year, $year - 1])
                ->select('eventyear', DB::raw('COUNT(*) as total_incidents'))
                ->groupBy('eventyear')
                ->get()
                ->keyBy('eventyear');

            $currentYear = $trendData->get($year)->total_incidents ?? 0;
            $previousYear = $trendData->get($year - 1)->total_incidents ?? 0;

            $trendText = $this->calculateTrendText($currentYear, $previousYear);

            // State benchmark with longer cache
            $stateAvgCacheKey = "state_avg_{$state}_{$year}";
            $stateAverage = Cache::remember($stateAvgCacheKey, 14400, function () use ($state, $year) {
                return DB::table('tbldataentry')
                    ->where('location', $state)
                    ->where('eventyear', $year)
                    ->selectRaw('COUNT(*) / COUNT(DISTINCT lga) as avg_val')
                    ->value('avg_val') ?? 0;
            });

            $benchmarkText = $this->calculateBenchmarkText($currentYear, $stateAverage);
            $mitigation = $this->getMitigationAdvice($topRisk);

            return "Advisory: {$trendText} {$benchmarkText} Given the prevalence of {$topRisk}, we recommend: {$mitigation}";
        });
    }



    // Helper methods for cleaner code
    private function calculateTrendText($current, $previous)
    {
        if ($previous > 0) {
            $percentChange = (($current - $previous) / $previous) * 100;
            if ($percentChange > 20) {
                return "Data indicates a sharp escalation in activity, with incidents rising by " . round($percentChange) . "% over the last year.";
            } elseif ($percentChange < -20) {
                return "The security environment shows signs of stabilization, with a " . abs(round($percentChange)) . "% decrease in recorded incidents.";
            }
            return "Activity levels remain consistent with the previous year.";
        }
        return $current > 0
            ? "Recent data indicates emergence of security incidents."
            : "No significant incident trends recorded.";
    }

    private function calculateBenchmarkText($lgaTotal, $stateAverage)
    {
        if ($lgaTotal > ($stateAverage * 1.5)) {
            return "This LGA is a high-priority zone, with incident volumes significantly above the state average.";
        } elseif ($lgaTotal < ($stateAverage * 0.5)) {
            return "Relative to the wider state, this area remains a lower-risk environment.";
        }
        return "Security risk levels are comparable to the state average.";
    }
    private function getMitigationAdvice($risk)
    {
        // Order matters: We list specific/high-severity items first.
        $strategies = [
            // --- VIOLENT THREATS ---
            'Terrorism' => "Terrorism threat elevated. Avoid crowded public spaces, government installations, and strengthen perimeter security at all operational bases.",
            'Insurgency' => "High insurgency risk. Suspend non-essential travel to remote areas and coordinate movement with state security forces.",
            'Militancy' => "Militant activity detected. Secure oil/gas infrastructure and maintain a safe standoff distance from waterways and creeks.",
            'Kidnapping' => "High kidnap risk. Implement strict journey management, vary travel routes/times, and utilize convoy security for high-profile staff.",
            'Piracy' => "Maritime threat. Ensure vessels maintain secure anchorage watches, harden decks, and adhere strictly to BMP West Africa guidelines.",
            'Armed Robbery' => "Criminality high. Upgrade physical security (lighting, perimeter walls), reduce cash handling, and conduct staff security awareness training.",
            'Communal' => "Inter-communal tension. Avoid areas with boundary disputes and maintain strict neutrality in local community engagements.",
            'Homicide' => "Violent crime spike. Avoid high-risk neighborhoods after dark and ensure all staff accommodation has reinforced access control.",

            // --- POLITICAL THREATS ---
            'Electoral' => "Election-related volatility. Avoid political rallies, campaign offices, and wearing partisan colors. Prepare for potential movement restrictions.",
            'Protest' => "Civil unrest likely. Monitor local news for gathering points, maintain a low profile, and prepare business continuity plans for supply chain disruptions.",
            'Civil Disturbance' => "Public disorder risk. Direct staff to shelter in place if unrest erupts. secure glass frontages and movable assets.",
            'Labour' => "Strike action possible. Engage with union representatives proactively and prepare contingency staffing plans.",
            'Corruption' => "Regulatory risk. Enforce strict anti-bribery compliance (FCPA/UKBA) and conduct internal audits on procurement processes.",
            'Impeachment' => "Political instability. Monitor legislative developments and prepare for potential governance vacuums or spontaneous demonstrations.",

            // --- PERSONAL THREATS ---
            'LEA Brutality' => "Law enforcement misconduct risk. Ensure all vehicle documentation is perfect. Instruct staff to remain compliant, calm, and use dashcams where legal.",
            'Trafficking' => "Human/Organ trafficking risk. Vet all domestic staff and travel agencies thoroughly. Report suspicious recruitment offers immediately.",
            'Assault' => "Personal safety risk. Carry out personal security training for staff and advise against walking alone in unlit or isolated areas.",
            'Rape' => "Gender-based violence risk. Review secure transportation for late shifts and ensure workplace harassment reporting lines are active.",
            'Suicide' => "Mental health indicator. Enhance employee welfare programs and provide access to confidential counseling services.",

            // --- PROPERTY THREATS ---
            'Cyber' => "Digital threat. Strengthen firewalls, enforce 2FA, and conduct regular phishing simulations for all employees.",
            'Arson' => "Fire sabotage risk. Install surveillance cameras around perimeters and ensure fire suppression systems are serviced and accessible.",
            'Sabotage' => "Infrastructure threat. Increase physical guarding around critical assets (generators, servers) and vet maintenance contractors.",
            'Burglary' => "Intrusion risk. Reinforce locks, install motion-sensor lighting, and test intrusion detection alarms regularly.",
            'Fraud' => "Financial crime risk. Implement multi-level approval processes for transactions and verify all vendor account changes via phone.",
            'Theft' => "Property crime. Implement asset tagging, clear desk policies, and access control logs for storage areas.",

            // --- SAFETY RISKS ---
            'Natural Disaster' => "Environmental hazard. Review evacuation plans for floods/storms and ensure emergency supplies (food/water/power) are stocked.",
            'Epidemic' => "Health crisis. Activate hygiene protocols, stockpile PPE, and prepare remote work infrastructure for non-essential staff.",
            'Accident' => "Transportation/Industrial risk. Enforce strict HSE compliance, mandate seatbelt usage, and conduct regular fleet maintenance checks.",
            'Fire' => "Fire outbreak risk. Conduct fire drills, clear emergency exits of obstructions, and inspect electrical wiring for faults.",
            'Route' => "Travel safety risk. Use route planning software to avoid known ambush points and travel only during daylight hours."
        ];

        // Loop through strategies to find a match
        foreach ($strategies as $key => $advice) {
            // Case-insensitive check (stripos)
            if (stripos($risk, $key) !== false) return $advice;
        }

        // Generic Fallback if the specific risk isn't found
        return "Maintain heightened situational awareness, monitor local news channels, and ensure emergency communication lines are active.";
    }
}
