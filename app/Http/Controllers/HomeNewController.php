<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use Illuminate\Http\Request;
use App\Models\CorrectionFactorForStates;
use App\Models\tblriskindicators;
use App\Models\DataInsights;
use App\Models\DataInsightsCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\CalculatesRisk;
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
     *
     * OPTIMISED:
     *  - Removed separate totalIncidents COUNT (derived from $data)
     *  - trendingRiskFactors now cached 30 min
     *  - recentIncidentsCount + auditedTime cached 5 min
     *  - homeInsights cached 1 h
     *  - calculateCompositeIndexByRiskFactors result is now cached inside the trait
     */
    public function getStateRiskReports(Request $request)
    {
        $securityIndexRows = $this->getCurrentCrimeRiskYearSecurityIndexRows();

        $year = (int) ($request->query('year') ?: now()->subYear()->year);

        // ── 1. Main aggregate (state × indicator for the year) ─────────────
        $data = Cache::remember("home_state_data:{$year}", 600, function () use ($year) {
            return tbldataentry::selectRaw("
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
        });

        // Derive totals from the already-loaded collection — no extra query needed
        $totalFatalities = (int) $data->sum('total_deaths');
        $totalIncidents  = (int) $data->sum('total_incidents'); // ← was a separate COUNT

        $stateRiskReports = $this->calculateStateRiskFromIndicators($data);

        // Top 3 most affected (by deaths) — PHP, zero queries
        $top3HighRiskStates = $data
            ->groupBy('location')
            ->map(fn($rows, $location) => [
                'location'   => $location,
                'human_toll' => $rows->sum('total_deaths'),
            ])
            ->sortByDesc('human_toll')
            ->take(3)
            ->pluck('location')
            ->implode(', ');

        // ── 2. Trending risk factors (cached 30 min) ───────────────────────
        $trendingRiskFactors = Cache::remember('home_trending_risk_factors', 1800, function () {
            return tbldataentry::selectRaw('riskindicators, COUNT(*) as frequency, SUM(victim) as total_victims, SUM(Casualties_count) as total_casualties')
                ->groupBy('riskindicators')
                ->orderByDesc('frequency')
                ->take(4)
                ->get();
        });

        // ── 3. Recent incidents stats (cached 5 min) ───────────────────────
        [$recentIncidentsCount, $auditedTime] = Cache::remember('home_recent_incident_stats', 300, function () {
            $now   = Carbon::now();
            $count = tbldataentry::where('audittimecreated', '>=', $now->copy()->subDay())->count();

            $latest = tbldataentry::latest('audittimecreated')->value('audittimecreated');
            $time   = $latest ? Carbon::parse($latest)->format('h:i:s A') : 'N/A';

            return [$count, $time];
        });

        // ── 4. Threat level (composite index is now cached inside the trait) ─
        $compositeIndexes = $this->calculateCompositeIndexByRiskFactors($year);

        $values = array_values($compositeIndexes);
        rsort($values);

        $avg    = !empty($values) ? array_sum($values) / count($values) : 0;
        $top5   = array_slice($values, 0, 5);
        $top5Avg = !empty($top5) ? array_sum($top5) / count($top5) : 0;

        $nationalScore    = ($top5Avg * 0.6) + ($avg * 0.4);
        $level            = $this->determineBusinessRiskLevel($nationalScore);
        $currentThreatLevel = match ($level) {
            1       => 'LOW',
            2       => 'MEDIUM',
            3       => 'HIGH',
            4       => 'VERY HIGH',
            default => 'LOW',
        };

        // ── 5. Insights (cached 1 h) ───────────────────────────────────────
        $homeInsights = Cache::remember('home_insights', 3600, function () {
            return DataInsights::with('category')->latest()->take(4)->get();
        });

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

    // ──────────────────────────────────────────────────────────────────────────
    // Insight pages
    // ──────────────────────────────────────────────────────────────────────────

    public function showDataInsights($id)
    {
        $post = is_numeric($id)
            ? DataInsights::findOrFail($id)
            : DataInsights::where('slug', $id)->orWhere('title', $id)->firstOrFail();

        $relatedCategoryPost = DataInsights::where('id', '!=', $post->id)->latest()->take(4)->get();

        $categories = Cache::remember('insight_categories', 3600, function () {
            return DataInsightsCategory::orderBy('name', 'asc')->get();
        });

        return view('showDataInsight', compact('post', 'relatedCategoryPost', 'categories'));
    }

    /**
     * All Insights Index Page
     */
    public function allInsights(Request $request)
    {
        $insights = DataInsights::with('category')->where('category_id', 2)->latest()->paginate(12);

        $categories = Cache::remember('insight_categories', 3600, function () {
            return DataInsightsCategory::orderBy('name', 'asc')->get();
        });

        return view('insights', compact('insights', 'categories'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function getStaticSecurityIndexRows(): array
    {
        return [
            ['location' => 'Abia',      'incident_count' => 42,  'nti_score' => 1.52,  'risk_level' => 'Low'],
            ['location' => 'Adamawa',   'incident_count' => 46,  'nti_score' => 1.63,  'risk_level' => 'Low'],
            ['location' => 'Akwa Ibom', 'incident_count' => 35,  'nti_score' => 0.68,  'risk_level' => 'Low'],
            ['location' => 'Anambra',   'incident_count' => 111, 'nti_score' => 2.64,  'risk_level' => 'Low'],
            ['location' => 'Bauchi',    'incident_count' => 47,  'nti_score' => 1.41,  'risk_level' => 'Low'],
            ['location' => 'Bayelsa',   'incident_count' => 16,  'nti_score' => 0.51,  'risk_level' => 'Low'],
            ['location' => 'Benue',     'incident_count' => 196, 'nti_score' => 11.29, 'risk_level' => 'High'],
            ['location' => 'Borno',     'incident_count' => 139, 'nti_score' => 8.76,  'risk_level' => 'High'],
        ];
    }

    private function getCrimeIndexIndicators()
    {
        return Cache::remember('indicators_crime', 86400, function () {
            try {
                return tblriskindicators::where('author', 'crimeIndex')
                    ->orderByRaw('CAST(indicators AS CHAR) ASC')
                    ->pluck('indicators');
            } catch (\Exception $e) {
                Log::error('CRITICAL: Could not fetch crime indicators. ' . $e->getMessage());
                return collect();
            }
        });
    }

    private function getCurrentCrimeRiskYearSecurityIndexRows(): array
    {
        $currentYear = (int) now()->year;
        $cacheKey    = 'crime_index_rows:' . $currentYear;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($currentYear) {
            $crimeIndicators = $this->getCrimeIndexIndicators();
            if ($crimeIndicators->isEmpty()) {
                return [];
            }

            $rawData = DB::table('tbldataentry')
                ->where('yy', $currentYear)
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->whereIn('riskindicators', $crimeIndicators->all())
                ->selectRaw('
                    TRIM(location) AS location,
                    yy,
                    COUNT(*) AS raw_incident_count,
                    COALESCE(SUM(Casualties_count), 0) AS raw_casualties_sum,
                    COALESCE(SUM(victim), 0) AS raw_victims_sum
                ')
                ->groupBy(DB::raw('TRIM(location)'), 'yy')
                ->get();

            $nciReports   = $this->calculateCrimeRiskIndexFromIndicators($rawData);
            $sortedReports = collect($nciReports)->sortBy('location')->take(8);

            $levelMap = [1 => 'Low', 2 => 'Medium', 3 => 'High', 4 => 'Very High'];
            $rows     = [];

            foreach ($sortedReports as $r) {
                $levelCode = $this->determineBusinessRiskLevel((float) $r['normalized_ratio_raw']);
                $rows[]    = [
                    'location'       => $r['location'],
                    'incident_count' => (int) $r['incident_count'],
                    'nci_score'      => round((float) $r['normalized_ratio'], 2),
                    'risk_level'     => $levelMap[$levelCode] ?? 'Unknown',
                ];
            }

            return $rows;
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Risk tool (analyze endpoint)
    // ──────────────────────────────────────────────────────────────────────────

    public function analyze(Request $request)
    {
        $lga   = $request->input('lga');
        $state = $request->input('state');
        $year  = $request->input('year', date('Y'));

        $stats = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->where('eventyear', $year)
            ->selectRaw('count(*) as total, sum(COALESCE(CAST(Casualties_count AS UNSIGNED), 0)) as casualties')
            ->first();

        $prevYearCount = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->where('eventyear', $year - 1)
            ->count();

        $trend = ($prevYearCount > 0)
            ? round((($stats->total - $prevYearCount) / $prevYearCount) * 100, 1)
            : 0;

        $topIndicator = DB::table('tbldataentry')
            ->where('lga', $lga)
            ->where('eventyear', $year)
            ->select('riskindicators', DB::raw('count(*) as count'))
            ->groupBy('riskindicators')
            ->orderBy('count', 'desc')
            ->first();

        $stateAvg = Cache::remember("state_avg_{$state}_{$year}", 3600, function () use ($state, $year) {
            return DB::table('tbldataentry')
                ->where('location', $state)
                ->where('eventyear', $year)
                ->selectRaw('count(*) / count(distinct lga) as avg_val')
                ->value('avg_val') ?? 0;
        });

        $recent = DB::table('tbldataentry')
            ->where('location', $state)
            ->orderBy('eventdateToUse', 'desc')
            ->limit(2)
            ->get(['eventdateToUse', 'add_notes as incident_description', 'lga']);

        $score = $this->calculateRiskScore($stats->total, $stats->casualties, $trend);

        return response()->json([
            'success'          => true,
            'score'            => $score,
            'total'            => $stats->total,
            'casualties'       => $stats->casualties ?? 0,
            'top_indicator'    => $topIndicator->riskindicators ?? 'N/A',
            'impact_level'     => $this->getImpactLabel($score),
            'state_avg'        => round($stateAvg, 1),
            'comparison'       => ($stats->total > $stateAvg) ? 'higher' : 'lower',
            'recent_incidents' => $recent,
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
        $baseScore       = min($total * 2, 40);
        $casualtyScore   = min($casualties * 3, 30);
        $trendScore      = min(abs($trend) / 2, 20);
        $volatilityScore = ($trend > 20 || $trend < -20) ? 10 : 0;

        return min(round($baseScore + $casualtyScore + $trendScore + $volatilityScore), 100);
    }

    public function requestReport(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $validated = $request->validate([
            'lga'   => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'email' => 'required|email|max:255',
        ]);

        $recipient = ReportRecipient::updateOrCreate(
            ['email' => $validated['email']],
            [
                'last_state_requested' => $validated['state'],
                'last_lga_requested'   => $validated['lga'],
                'last_request_at'      => now(),
                'request_count'        => DB::raw('request_count + 1'),
            ]
        );

        GenerateRiskReportJob::dispatch(
            $validated['lga'],
            $validated['state'],
            $year,
            $validated['email']
        );

        return response()->json([
            'success' => true,
            'message' => "Request received! We are generating the report for {$validated['lga']} and will email it to {$validated['email']} within a few minutes.",
        ], 200);
    }

    private function getSmartAdvisoryOptimized($lga, $state, $topRisk, $year)
    {
        $advisoryCacheKey = "advisory_{$lga}_{$year}";

        return Cache::remember($advisoryCacheKey, 7200, function () use ($lga, $state, $topRisk, $year) {
            $trendData = DB::table('tbldataentry')
                ->where('lga', $lga)
                ->whereIn('eventyear', [$year, $year - 1])
                ->select('eventyear', DB::raw('COUNT(*) as total_incidents'))
                ->groupBy('eventyear')
                ->get()
                ->keyBy('eventyear');

            $currentYear  = $trendData->get($year)->total_incidents    ?? 0;
            $previousYear = $trendData->get($year - 1)->total_incidents ?? 0;

            $trendText = $this->calculateTrendText($currentYear, $previousYear);

            $stateAverage = Cache::remember("state_avg_{$state}_{$year}", 14400, function () use ($state, $year) {
                return DB::table('tbldataentry')
                    ->where('location', $state)
                    ->where('eventyear', $year)
                    ->selectRaw('COUNT(*) / COUNT(DISTINCT lga) as avg_val')
                    ->value('avg_val') ?? 0;
            });

            $benchmarkText = $this->calculateBenchmarkText($currentYear, $stateAverage);
            $mitigation    = $this->getMitigationAdvice($topRisk);

            return "Advisory: {$trendText} {$benchmarkText} Given the prevalence of {$topRisk}, we recommend: {$mitigation}";
        });
    }

    private function calculateTrendText($current, $previous)
    {
        if ($previous > 0) {
            $percentChange = (($current - $previous) / $previous) * 100;
            if ($percentChange > 20) {
                return 'Data indicates a sharp escalation in activity, with incidents rising by ' . round($percentChange) . '% over the last year.';
            } elseif ($percentChange < -20) {
                return 'The security environment shows signs of stabilization, with a ' . abs(round($percentChange)) . '% decrease in recorded incidents.';
            }
            return 'Activity levels remain consistent with the previous year.';
        }
        return $current > 0
            ? 'Recent data indicates emergence of security incidents.'
            : 'No significant incident trends recorded.';
    }

    private function calculateBenchmarkText($lgaTotal, $stateAverage)
    {
        if ($lgaTotal > ($stateAverage * 1.5)) {
            return 'This LGA is a high-priority zone, with incident volumes significantly above the state average.';
        } elseif ($lgaTotal < ($stateAverage * 0.5)) {
            return 'Relative to the wider state, this area remains a lower-risk environment.';
        }
        return 'Security risk levels are comparable to the state average.';
    }

    private function getMitigationAdvice($risk)
    {
        $strategies = [
            'Terrorism'        => 'Terrorism threat elevated. Avoid crowded public spaces, government installations, and strengthen perimeter security at all operational bases.',
            'Insurgency'       => 'High insurgency risk. Suspend non-essential travel to remote areas and coordinate movement with state security forces.',
            'Militancy'        => 'Militant activity detected. Secure oil/gas infrastructure and maintain a safe standoff distance from waterways and creeks.',
            'Kidnapping'       => 'High kidnap risk. Implement strict journey management, vary travel routes/times, and utilize convoy security for high-profile staff.',
            'Piracy'           => 'Maritime threat. Ensure vessels maintain secure anchorage watches, harden decks, and adhere strictly to BMP West Africa guidelines.',
            'Armed Robbery'    => 'Criminality high. Upgrade physical security (lighting, perimeter walls), reduce cash handling, and conduct staff security awareness training.',
            'Communal'         => 'Inter-communal tension. Avoid areas with boundary disputes and maintain strict neutrality in local community engagements.',
            'Homicide'         => 'Violent crime spike. Avoid high-risk neighborhoods after dark and ensure all staff accommodation has reinforced access control.',
            'Electoral'        => 'Election-related volatility. Avoid political rallies, campaign offices, and wearing partisan colors. Prepare for potential movement restrictions.',
            'Protest'          => 'Civil unrest likely. Monitor local news for gathering points, maintain a low profile, and prepare business continuity plans for supply chain disruptions.',
            'Civil Disturbance' => 'Public disorder risk. Direct staff to shelter in place if unrest erupts. Secure glass frontages and movable assets.',
            'Labour'           => 'Strike action possible. Engage with union representatives proactively and prepare contingency staffing plans.',
            'Corruption'       => 'Regulatory risk. Enforce strict anti-bribery compliance (FCPA/UKBA) and conduct internal audits on procurement processes.',
            'Impeachment'      => 'Political instability. Monitor legislative developments and prepare for potential governance vacuums or spontaneous demonstrations.',
            'LEA Brutality'    => 'Law enforcement misconduct risk. Ensure all vehicle documentation is perfect. Instruct staff to remain compliant, calm, and use dashcams where legal.',
            'Trafficking'      => 'Human/Organ trafficking risk. Vet all domestic staff and travel agencies thoroughly. Report suspicious recruitment offers immediately.',
            'Assault'          => 'Personal safety risk. Carry out personal security training for staff and advise against walking alone in unlit or isolated areas.',
            'Rape'             => 'Gender-based violence risk. Review secure transportation for late shifts and ensure workplace harassment reporting lines are active.',
            'Suicide'          => 'Mental health indicator. Enhance employee welfare programs and provide access to confidential counseling services.',
            'Cyber'            => 'Digital threat. Strengthen firewalls, enforce 2FA, and conduct regular phishing simulations for all employees.',
            'Arson'            => 'Fire sabotage risk. Install surveillance cameras around perimeters and ensure fire suppression systems are serviced and accessible.',
            'Sabotage'         => 'Infrastructure threat. Increase physical guarding around critical assets (generators, servers) and vet maintenance contractors.',
            'Burglary'         => 'Intrusion risk. Reinforce locks, install motion-sensor lighting, and test intrusion detection alarms regularly.',
            'Fraud'            => 'Financial crime risk. Implement multi-level approval processes for transactions and verify all vendor account changes via phone.',
            'Theft'            => 'Property crime. Implement asset tagging, clear desk policies, and access control logs for storage areas.',
            'Natural Disaster' => 'Environmental hazard. Review evacuation plans for floods/storms and ensure emergency supplies (food/water/power) are stocked.',
            'Epidemic'         => 'Health crisis. Activate hygiene protocols, stockpile PPE, and prepare remote work infrastructure for non-essential staff.',
            'Accident'         => 'Transportation/Industrial risk. Enforce strict HSE compliance, mandate seatbelt usage, and conduct regular fleet maintenance checks.',
            'Fire'             => 'Fire outbreak risk. Conduct fire drills, clear emergency exits of obstructions, and inspect electrical wiring for faults.',
            'Route'            => 'Travel safety risk. Use route planning software to avoid known ambush points and travel only during daylight hours.',
        ];

        foreach ($strategies as $key => $advice) {
            if (stripos($risk, $key) !== false) return $advice;
        }

        return 'Maintain heightened situational awareness, monitor local news channels, and ensure emergency communication lines are active.';
    }
}
