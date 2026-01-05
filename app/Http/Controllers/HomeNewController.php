<?php

namespace App\Http\Controllers;

use App\Models\tbldataentry;
use Illuminate\Http\Request;
use App\Models\CorrectionFactorForStates;
use Carbon\Carbon;
use App\Models\tblriskindicators;
use App\Models\DataInsights;
use App\Models\DataInsightsCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\CalculatesRisk; // Import the Trait

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
        $now = \Carbon\Carbon::now();
        $recentIncidentsCount = tbldataentry::where('audittimecreated', '>=', $now->subDay())->count();
        $latestIncidentTime = tbldataentry::latest('audittimecreated')->first();
        $auditedTime = $latestIncidentTime ? \Carbon\Carbon::parse($latestIncidentTime->audittimecreated)->format('h:i:s A') : 'N/A';

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

        $assessmentScope = $highRiskStateCount >= 5 ? 'Nationwide Assessment' : 'Regional Assessment';
        $validUntil = $latestIncidentTime ? Carbon::parse($latestIncidentTime->audittimecreated)->addHours(24)->format('M d, Y h:i A') : 'N/A';
        $keyConcerns = implode(', ', $trendingRiskFactors->pluck('riskindicators')->take(3)->toArray());

        // 8. Prepare Map and Dropdown Data
        $riskData = $this->riskIndex($request);
        $calculatorStates = tbldataentry::select('location')->distinct()->orderBy('location')->pluck('location');

        try {
            $calculatorIndustries = DB::table('business_risk_industries')->orderBy('name')->pluck('name');
        } catch (\Exception $e) {
            $calculatorIndustries = ['Oil & Gas', 'Banking', 'Telecoms', 'Manufacturing', 'FMCG', 'Logistics'];
        }

        // 9. Fetch Insights
        $homeInsights = DataInsights::with('category')->latest()->take(4)->get();

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
                'assessmentScope',
                'validUntil',
                'keyConcerns',
                'homeInsights',
                'calculatorStates',
                'calculatorIndustries'
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
}
