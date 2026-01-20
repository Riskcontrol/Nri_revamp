<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        // All reports listed equally
        $reports = [
            [
                'id' => 1,
                'title' => 'Security In Nigeria Over the Past 7 Years',
                'period' => '2018 – 2024',
                'description' => "Nigeria's security landscape from 2018 to 2024 reveals a counterintuitive reality - incidents increased significantly by 160.8%, yet annual deaths have fallen by 41.0%. This comprehensive report is based on 25,945 verified incidents.",
                'image' => 'images/download.png',
                'download_link' => route('reports.download', ['id' => 1]),
            ],
            // [
            //     'id' => 2,
            //     'title' => '2023 Annual Security Review',
            //     'period' => 'Jan 2023 – Dec 2023',
            //     'description' => 'An in-depth analysis of security trends across all 36 states during the 2023 election year, highlighting key shifts in political violence and banditry.',
            //     'image' => 'images/mobile.png',
            //     'download_link' => route('reports.download', ['id' => 2]),
            // ],
            // [
            //     'id' => 3,
            //     'title' => 'Q1 2024 Risk Outlook',
            //     'period' => 'Jan 2024 – Mar 2024',
            //     'description' => 'Early warning signals and forecast for the first quarter of 2024. Focuses on emerging threats in the North West and economic-driven unrest.',
            //     'image' => 'images/map_risk.png',
            //     'download_link' => route('reports.download', ['id' => 3]),
            // ],
        ];

        return view('reports.index', compact('reports'));
    }

    public function download($id = null)
    {
        return redirect()->back()->with('success', 'Download started...');
    }
}
