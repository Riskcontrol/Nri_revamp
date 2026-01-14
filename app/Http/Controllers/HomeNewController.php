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

class HomeNewController extends Controller
{
    use CalculatesRisk; // Use the weighted calculation logic

    /**
     * Main route for the Homepage Dashboard
     */
    public function getStateRiskReports(Request $request)
    {
        // 1. Fetch data for the weighted trait
        // We now include 'riskindicators' to allow for weighted severity
        $data = tbldataentry::selectRaw('location, riskindicators, yy, COUNT(*) as total_incidents, SUM(Casualties_count) as total_deaths, SUM(victim) as total_victims')
            ->groupBy('location', 'riskindicators', 'yy')
            ->get();

        // 2. Calculate state risk reports using the Trait
        $stateRiskReports = $this->calculateWeightedStateRisk($data);

        // 3. Filter high-risk states (Level 3 and 4)
        $highRiskStates = collect($stateRiskReports)->filter(function ($report) {
            return in_array($report['risk_level'], [3, 4]);
        });

        $highRiskStateCount = $highRiskStates->count();

        $top3HighRiskStates = $data
            ->filter(function ($row) {
                $indicator = strtolower(trim($row->riskindicators));
                // Specifically tracking the most severe threat indicators
                return in_array($indicator, ['terrorism', 'kidnapping']);
            })
            ->groupBy('location')
            ->map(function ($rows, $location) {
                $deaths = $rows->sum('total_deaths');
                $injuries = $rows->sum('total_injuries');

                // The Human Toll formula: Fatalities are the primary weight
                return [
                    'location' => $location,
                    'human_toll' => ($deaths * 1.0) + ($injuries * 0.5)
                ];
            })
            ->sortByDesc('human_toll')
            ->take(3)
            ->pluck('location')
            ->implode(', ');

        // 4. Trending Risk Factors
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
        $totalIncidents = tbldataentry::where('yy', 2025)->count();

        // 7. Current Threat Level calculation
        // Based on the highest normalized ratio produced by the trait
        // $highestNormalized = collect($stateRiskReports)->max('normalized_ratio');
        // This will list only the ratios, e.g. ["Abia" => 2.12, "Lagos" => 15.5, ...]
        // dd(collect($stateRiskReports)->pluck('normalized_ratio', 'location'));

        $maxScore = collect($stateRiskReports)->max('normalized_ratio');
        $avgScore = collect($stateRiskReports)->avg('normalized_ratio');

        $compositeNationalScore = ($maxScore * 0.5) + ($avgScore * 0.5);
        // dd($compositeNationalScore);

        if ($compositeNationalScore <= 4.0) {
            $currentThreatLevel = 'LOW';
        } elseif ($compositeNationalScore <= 6.0) {
            $currentThreatLevel = 'MEDIUM';
        } elseif ($compositeNationalScore <= 8.5) {
            $currentThreatLevel = 'HIGH';
        } else {
            $currentThreatLevel = 'CRITICAL';
        }


        // 8. Prepare Map and Dropdown Data
        $riskData = $this->riskIndex($request);

        // 9. Fetch Insights
        $homeInsights = DataInsights::with('category')->latest()->take(4)->get();

        $states = collect(config('nigeria'));

        // 10. Pass all data to view
        return view('home', array_merge(
            compact(
                'highRiskStateCount',
                'top3HighRiskStates',
                'trendingRiskFactors',
                'recentIncidentsCount',
                'auditedTime',
                'totalIncidents',
                'currentThreatLevel',
                'homeInsights',
                'states',
            ),
            $riskData
        ));
    }

    /**
     * Homepage Risk Calculator API Logic
     */
    public function calculateHomepageRisk(Request $request)
    {
        $state = $request->state;
        $industry = $request->industry;
        $measures = $request->input('measures', []);
        $year = now()->year;

        $query = DB::table('business_risk_data')
            ->where('location', $state)
            ->where('year', $year)
            ->where(function ($q) use ($industry) {
                $q->where('industry', $industry)->orWhere('industry', 'LIKE', "%$industry%");
            });

        $incidents = $query->get();

        $rawScore = 0;
        foreach ($incidents as $incident) {
            $severity = strtolower($incident->level ?? $incident->impact ?? 'low');
            if (str_contains($severity, 'high') || str_contains($severity, 'critical')) {
                $rawScore += 20;
            } elseif (str_contains($severity, 'medium')) {
                $rawScore += 10;
            } else {
                $rawScore += 5;
            }
        }

        $baseRisk = min($rawScore, 100);
        $reduction = 0;
        if (in_array('personnel', $measures)) $reduction += 0.20;
        if (in_array('cctv', $measures))      $reduction += 0.10;
        if (in_array('access', $measures))    $reduction += 0.10;
        if (in_array('protocols', $measures)) $reduction += 0.05;

        $totalReduction = min($reduction, 0.50);
        $finalScore = round($baseRisk * (1 - $totalReduction));

        $label = match (true) {
            $finalScore >= 75 => 'Critical',
            $finalScore >= 50 => 'High',
            $finalScore >= 25 => 'Medium',
            default => 'Low'
        };

        return response()->json([
            'success' => true,
            'base_risk' => $baseRisk,
            'final_score' => $finalScore,
            'label' => $label,
            'savings' => round($baseRisk - $finalScore),
            'incident_count' => $incidents->count()
        ]);
    }

    /**
     * Map and Violence Index Processing
     */
    public function riskIndex(Request $request, $risk = null)
    {
        $maxYear = $this->getMaxYear() - 1;
        $maxMonth = $this->getMaxMonth($maxYear);
        $indicatorName = "Nigeria Violence Index";
        $violentRiskindicators = $this->getViolentIndicators();

        if ($request->input('riskindicator') && $request->input('riskindicator') != 'violence-index') {
            $violentRiskindicators = $request->input('riskindicator');
            $indicatorName = $violentRiskindicators;
        }

        $dataByState = $this->getDataByStateWithoutMonth($maxYear, $violentRiskindicators);
        $dataByState = $this->calculateData($dataByState); // Temporary use until fully migrated

        $searchDuration = $this->getStartEndTime($maxYear, "01", "01", $this->getMaxYear(), $maxMonth, date('d'));
        $locations = $this->leafletData($maxYear);
        $limit = 8;
        $dataByState = collect($dataByState)->sortByDesc('normalized_ratio')->take($limit);

        return [
            'indicatorName' => $indicatorName,
            'dataByState' => $dataByState,
            'maxYear' => $maxYear,
            'searchDuration' => $searchDuration,
            'locations' => $locations
        ];
    }

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
    private function getMaxYear()
    {
        return tbldataentry::where('riskfactors', 'Violent Threats')->max('eventyear');
    }
    private function getMaxMonth($maxYear)
    {
        return tbldataentry::where('riskfactors', 'Violent Threats')->where('eventyear', $maxYear)->max('eventmonth');
    }
    private function getViolentIndicators()
    {
        return Tblriskindicators::where('factors', 'Violent Threats')->pluck('indicators')->toArray();
    }

    private function getDataByStateWithoutMonth($maxYear, $indicators = null)
    {
        $indicators = is_null($indicators) ? [] : (is_array($indicators) ? $indicators : [$indicators]);
        return DB::table(DB::raw('(SELECT DISTINCT location FROM tbldataentry) as locations'))
            ->leftJoin('tbldataentry', function ($join) use ($maxYear, $indicators) {
                $join->on('locations.location', '=', 'tbldataentry.location')
                    ->whereIn('tbldataentry.riskindicators', $indicators)
                    ->where('tbldataentry.eventyear', $maxYear);
            })
            ->select(
                'locations.location',
                DB::raw('COALESCE(SUM(tbldataentry.Casualties_count), 0) as sum_casualties'),
                DB::raw('COALESCE(SUM(tbldataentry.victim), 0) as sum_victims'),
                DB::raw('COALESCE(COUNT(tbldataentry.location), 0) as incident_count')
            )
            ->groupBy('locations.location')->get();
    }

    private function calculateData($data)
    {
        // This remains for backward compatibility with existing map markers
        $reports = [];
        $AllIncidentCount = $data->sum('incident_count');
        $AllvictimCount = $data->sum('sum_victims');
        $AlldeathThreatsCount = $data->sum('sum_casualties');

        foreach ($data as $item) {
            $totalRatio = ($AllIncidentCount != 0 ? ($item->incident_count / $AllIncidentCount) * 25 : 0) +
                ($AllvictimCount != 0 ? ($item->sum_victims / $AllvictimCount) * 35 : 0) +
                ($AlldeathThreatsCount != 0 ? ($item->sum_casualties / $AlldeathThreatsCount) * 40 : 0);

            $reports[$item->location] = [
                'location' => $item->location,
                'incident_count' => $item->incident_count,
                'sum_victims' => $item->sum_victims,
                'sum_casualties' => $item->sum_casualties,
                'total_ratio' => $totalRatio,
            ];
        }
        return $reports;
    }

    private function getStartEndTime($maxYear, $maxMonth, $eventday, $maxYearEnd, $maxMonthEnd, $eventdayEnd)
    {
        $startDate = $maxYear . "-" . ($maxMonth ?: '01') . "-" . ($eventday ?: '01');
        $endDate = ($maxYearEnd ?: $maxYear) . "-" . ($maxMonthEnd ?: '12') . "-" . ($eventdayEnd ?: '28');
        return date('d F Y', strtotime($startDate)) . ' - ' . date('d F Y', strtotime($endDate));
    }

    private function leafletData($maxYear)
    {
        return tbldataentry::where('yy', $maxYear)->get()->map(fn($entry) => [
            'state' => $entry->location,
            'latitude' => $entry->latitude,
            'longitude' => $entry->longitude,
            'color' => 'red',
            'riskindicators' => $entry->riskindicators,
            'impact' => $entry->impact
        ]);
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
        if ($score >= 25) return 'Moderate';
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
        // Increase execution time at PHP level instead
        set_time_limit(120);
        ini_set('max_execution_time', 120);

        try {
            // Validate
            $validated = $request->validate([
                'state' => 'required|string',
                'lga' => 'required|string',
                'email' => 'required|email',
                'year' => 'nullable|integer|min:2018|max:' . date('Y')
            ]);

            $state = $validated['state'];
            $lga = $validated['lga'];
            $year = $validated['year'] ?? date('Y');

            \Log::info("PDF Generation Started", ['lga' => $lga, 'year' => $year]);

            // ============================================
            // Get Data with Caching
            // ============================================
            $cacheKey = "pdf_data_{$lga}_{$year}";

            $reportData = Cache::remember($cacheKey, 1800, function () use ($lga, $year) {

                $baseData = DB::table('tbldataentry as t')
                    ->leftJoin('state_neighbourhoods as sn', 't.neighbourhood', '=', 'sn.id')
                    ->where('t.lga', $lga)
                    ->where('t.eventyear', $year)
                    ->select(
                        't.riskindicators',
                        't.Casualties_count',
                        't.victim',
                        't.eventdate',
                        't.add_notes',
                        't.eventdateToUse',
                        'sn.neighbourhood_name'
                    )
                    ->get();

                if ($baseData->isEmpty()) {
                    return null;
                }

                // Process data
                $riskDistribution = $baseData
                    ->groupBy('riskindicators')
                    ->map(fn($group) => (object)[
                        'riskindicators' => $group->first()->riskindicators ?? 'Unknown',
                        'count' => $group->count()
                    ])
                    ->sortByDesc('count')
                    ->take(5)
                    ->values();

                $casualties = (object)[
                    'deaths' => $baseData->sum(function ($row) {
                        return is_numeric($row->Casualties_count) ? (int)$row->Casualties_count : 0;
                    }),
                    'kidnaps' => $baseData->sum(function ($row) {
                        return is_numeric($row->victim) ? (int)$row->victim : 0;
                    })
                ];

                $hotspots = $baseData
                    ->filter(fn($row) => !empty($row->neighbourhood_name))
                    ->groupBy('neighbourhood_name')
                    ->map(fn($group, $name) => (object)[
                        'neighbourhood_name' => $name,
                        'incidents' => $group->count()
                    ])
                    ->sortByDesc('incidents')
                    ->take(4)
                    ->values();

                $incidents = $baseData
                    ->sortByDesc('eventdateToUse')
                    ->take(5)
                    ->values();

                return [
                    'riskDistribution' => $riskDistribution,
                    'casualties' => $casualties,
                    'hotspots' => $hotspots,
                    'incidents' => $incidents,
                    'topRisk' => $riskDistribution->first()->riskindicators ?? 'General Insecurity'
                ];
            });

            // Check if no data
            if (!$reportData) {
                return response()->json([
                    'success' => false,
                    'message' => "No data available for {$lga} in {$year}."
                ], 422);
            }

            // ============================================
            // Generate Simple Advisory
            // ============================================
            $topRisk = $reportData['topRisk'];
            $mitigation = $this->getMitigationAdvice($topRisk);

            $advisory = "Security Advisory: Based on {$year} data for {$lga}, the primary risk factor is {$topRisk}. {$mitigation}";

            // ============================================
            // Generate PDF (FIXED)
            // ============================================
            $pdf = Pdf::loadView('reports.risk_profile', [
                'state' => $state,
                'lga' => $lga,
                'year' => $year,
                'riskDistribution' => $reportData['riskDistribution'],
                'casualties' => $reportData['casualties'],
                'hotspots' => $reportData['hotspots'],
                'incidents' => $reportData['incidents'],
                'advisory' => $advisory
            ]);

            // CORRECTED: Only use valid methods
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('isRemoteEnabled', false);

            \Log::info("PDF generated successfully");

            return $pdf->download(Str::slug("Risk_Report_{$lga}_{$year}") . '.pdf');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getSmartAdvisoryOptimized($lga, $state, $topRisk, $year)
    {
        // Cache advisory components separately
        $advisoryCacheKey = "advisory_{$lga}_{$year}";

        return Cache::remember($advisoryCacheKey, 3600, function () use ($lga, $state, $topRisk, $year) {

            // SINGLE QUERY for all advisory data
            $advisoryData = DB::table('tbldataentry')
                ->where('lga', $lga)
                ->where('eventyear', '>=', $year - 1) // Get 2 years of data
                ->selectRaw('
                COUNT(*) as total_incidents,
                eventyear,
                QUARTER(eventdateToUse) as quarter,
                COUNT(DISTINCT lga) as lga_count
            ')
                ->groupBy('eventyear', 'quarter')
                ->get();

            // Calculate trend from aggregated data
            $currentYear = $advisoryData->where('eventyear', $year)->sum('total_incidents');
            $previousYear = $advisoryData->where('eventyear', $year - 1)->sum('total_incidents');

            $trendText = $this->calculateTrendText($currentYear, $previousYear);

            // State benchmark (simplified)
            $stateAvgCacheKey = "state_avg_{$state}_{$year}";
            $stateAverage = Cache::remember($stateAvgCacheKey, 7200, function () use ($state, $year) {
                return DB::table('tbldataentry')
                    ->where('location', $state)
                    ->where('eventyear', $year)
                    ->selectRaw('COUNT(*) / COUNT(DISTINCT lga) as avg_val')
                    ->value('avg_val') ?? 0;
            });

            $lgaTotal = $currentYear;
            $benchmarkText = $this->calculateBenchmarkText($lgaTotal, $stateAverage);

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
