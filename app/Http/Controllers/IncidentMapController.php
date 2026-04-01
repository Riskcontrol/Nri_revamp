<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IncidentMapController extends Controller
{
    // =========================================================================
    // RISK FACTOR → COLOUR MAP
    //
    // Keyed EXACTLY on the 5 values in tblriskfactors.name, which is what
    // gets written into tbldataentry.riskfactors at import time:
    //
    //   ID  3 → Violent Threats   (weight .60)
    //   ID 15 → Property Threats  (weight .10)
    //   ID 16 → Personal Threats  (weight .10)
    //   ID 17 → Safety            (weight .10)
    //   ID 18 → Political Threats (weight .10)
    //
    // Note: riskindicators (Terrorism, Kidnapping, Armed Robbery, etc.) is the
    // sub-type and is displayed in the popup — it does NOT drive the colour.
    // =========================================================================

    private const FACTOR_COLORS = [
        'Violent Threats'   => [
            'color' => '#ef4444',   // red    — highest weight (.60), most severe
            'label' => 'Violent Threats',
        ],
        'Political Threats' => [
            'color' => '#f97316',   // orange — civil/electoral tension
            'label' => 'Political Threats',
        ],
        'Personal Threats'  => [
            'color' => '#a855f7',   // purple — individual-targeted crime
            'label' => 'Personal Threats',
        ],
        'Property Threats'  => [
            'color' => '#eab308',   // amber  — asset/property crime
            'label' => 'Property Threats',
        ],
        'Safety'            => [
            'color' => '#22d3ee',   // cyan   — accidents, fire, disaster
            'label' => 'Safety',
        ],
    ];

    // Grey fallback for any unexpected / legacy riskfactors value
    private const FALLBACK_COLOR = ['color' => '#94a3b8', 'label' => 'Other'];

    // =========================================================================
    // GEOJSON ENDPOINT  —  GET /api/incidents/geojson
    // =========================================================================

    public function geojson(): JsonResponse
    {
        $features = Cache::remember('incident_map_geojson_7d', 600, function () {

            $centroids = config('nigeria_centroids', []);

            $rows = DB::table('tbldataentry as e')
                ->leftJoin('tblweeklydataentry as w', 'w.eventid', '=', 'e.eventid')
                ->where('e.eventdateToUse', '>=', now()->subDays(7)->format('Y-m-d'))
                ->whereNotNull('e.location')
                ->where('e.location', '!=', '')
                ->select([
                    'e.eventid',
                    'e.eventdateToUse',
                    'e.location',
                    'e.lga',
                    'e.caption',
                    'e.riskfactors',
                    'e.riskindicators',
                    'e.impact',
                    'e.Casualties_count',
                    'e.Injuries_count',
                    'e.add_notes',
                    'e.latitude',
                    'e.longitude',
                    DB::raw("COALESCE(w.news, 'No') as is_breaking"),
                ])
                ->orderByDesc('e.eventdateToUse')
                ->limit(300)
                ->get();

            $features = [];

            foreach ($rows as $row) {

                // ── Resolve coordinates ───────────────────────────────────────
                $lat = (float) ($row->latitude  ?? 0);
                $lng = (float) ($row->longitude ?? 0);

                if ($lat === 0.0 && $lng === 0.0) {
                    $state    = trim($row->location ?? '');
                    $centroid = $centroids[$state] ?? null;
                    if (! $centroid) {
                        continue; // no coordinates at all — skip this row
                    }
                    $lat = $centroid['lat'];
                    $lng = $centroid['lng'];
                }

                // ── Map riskfactors → colour ──────────────────────────────────
                $factorKey = trim($row->riskfactors ?? '');
                $factorCfg = self::FACTOR_COLORS[$factorKey] ?? self::FALLBACK_COLOR;

                $features[] = [
                    'type'     => 'Feature',
                    'geometry' => [
                        'type'        => 'Point',
                        'coordinates' => [$lng, $lat],
                    ],
                    'properties' => [
                        'eventid'      => $row->eventid,
                        'date'         => $row->eventdateToUse,
                        'state'        => trim($row->location     ?? ''),
                        'lga'          => trim($row->lga          ?? ''),
                        'caption'      => $row->caption,
                        'factor'       => $factorKey,           // one of the 5 DB categories
                        'factor_label' => $factorCfg['label'],
                        'factor_color' => $factorCfg['color'],
                        'indicator'    => trim($row->riskindicators ?? ''), // sub-type in popup
                        'casualties'   => (int) ($row->Casualties_count ?? 0),
                        'injuries'     => (int) ($row->Injuries_count   ?? 0),
                        'impact'       => $row->impact,
                        'summary'      => $row->add_notes,
                        'is_breaking'  => ($row->is_breaking === 'Yes'),
                    ],
                ];
            }

            return $features;
        });

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
            'legend'   => self::FACTOR_COLORS,
        ])->header('Cache-Control', 'public, max-age=600');
    }
}
