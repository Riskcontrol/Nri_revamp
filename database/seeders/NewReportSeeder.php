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
                'title'       => 'Half-Year Security Report',
                'period'      => 'H1 2025 (January - June)',
                'description' => "The Nigeria Risk Index (NRI) Half-Year Report for H1 2025 documents a significant security concer",
                'file_path'   => 'reports/H1-2025.pdf',      // ✅ was: private/reports/Q1-2025.pdf
                'image_path'  => 'images/reports/half-year.png',
                'min_tier'    => 1,
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
