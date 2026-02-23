<?php

namespace App\Http\Controllers;

use App\Models\NewReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $reports = NewReport::query()
            ->where('is_published', true)
            ->latest()
            ->get();

        return view('reports.index', compact('reports'));
    }



    public function download(NewReport $report, Request $request)
    {
        $user = $request->user(); // guaranteed by auth.interact middleware

        // Tier check
        if ((int) $user->tier < (int) $report->min_tier) {
            return redirect()
                ->route('reports.index')
                ->with('tier_lock', [
                    'title'        => 'Premium Access Required',
                    'subtitle'     => 'This report is locked on your plan.',
                    'message'      => "Upgrade to Tier {$report->min_tier}+ to download this report.",
                    'label1'       => 'Locked report',
                    'locked_item'  => $report->title,
                    'label2'       => 'Required tier',
                    'when'         => "Tier {$report->min_tier}+",
                    'footer'       => 'Contact us to upgrade your plan.',
                    'cta_url'      => route('enterprise-access.create'),
                ]);
        }

        // Security: ensure the resolved file is exactly what the DB record says
        // (guards against any manipulation of the stored path value)
        $storagePath = $report->file_path;

        // Prevent path traversal - the stored path must not contain '..'
        if (str_contains($storagePath, '..')) {
            abort(403, 'Invalid file path.');
        }

        abort_unless(Storage::disk('local')->exists($storagePath), 404);

        // Serve with a clean filename (report title, not the internal path)
        $filename = $report->title . '.pdf';

        return Storage::disk('local')->download($storagePath, $filename);
    }
}
