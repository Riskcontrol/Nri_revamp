<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewReport;
use Illuminate\Support\Str;

class NewReportSeeder extends Seeder
{
    public function run(): void
    {
        $reports = [
            [
                'title'       => 'Quarterly Security Report- Q1 2025',
                'period'      => 'Q1 2025',
                'description' => "The first quarter of 2025 marks a concerning escalation in Nigeria's security landscape",
                'file_path'   => 'reports/Q1-2025.pdf',      // ✅ was: private/reports/Q1-2025.pdf
                'image_path'  => 'images/reports/download.png',
                'min_tier'    => 1,
            ],
            [
                'title'       => 'Quarterly Security Report - Q2 2025',
                'period'      => 'Q2 2025',
                'description' => "The Nigeria Risk Index (NRI) report for Q2 2025 ...",
                'file_path'   => 'reports/Q2-2025.pdf',      // ✅ was: private/reports/Q2-2025.pdf
                'image_path'  => 'images/reports/download.png',
                'min_tier'    => 1,
            ],
            [
                'title'       => 'ANNUAL REPORT 2025',
                'period'      => '2025',
                'description' => 'A Comprehensive Analysis of Security Threats...',
                'file_path'   => 'reports/NIGERIA-RISK-INDEX-2.pdf', // ✅ was: private/reports/NIGERIA-RISK-INDEX-2.pdf
                'image_path'  => 'images/reports/NIGERIA-RISK-INDEX-2.png',
                'min_tier'    => 2,
            ],
        ];

        foreach ($reports as $report) {
            NewReport::updateOrCreate(
                ['slug' => Str::slug($report['title'])],
                array_merge($report, [
                    'slug' => Str::slug($report['title']),
                    'is_published' => true,
                ])
            );
        }
    }
}
