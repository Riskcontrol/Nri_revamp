<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class AdminReportController extends Controller
{
    // =========================================================================
    // INDEX
    // =========================================================================

    public function index()
    {
        $reports = NewReport::orderByDesc('created_at')->get();

        return view('admin.reports.index', compact('reports'));
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public function create()
    {
        return view('admin.reports.create');
    }

    // =========================================================================
    // STORE
    //
    // PDF  → private 'local' disk  (storage/app/private/reports/)
    //        Never publicly accessible. Served only via the authenticated
    //        ReportController::download() which checks tier before streaming.
    //
    // Thumbnail → public/images/reports/
    //        Stored directly in the public folder via move() so asset() works
    //        without a storage symlink. Matches the existing convention where
    //        seeded reports use asset('images/reports/half-year.png').
    // =========================================================================

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'period'       => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:2000',
            'min_tier'     => 'required|integer|in:1,2,3',
            'is_published' => 'nullable|boolean',
            'pdf_file'     => ['required', File::types(['pdf'])->max(50 * 1024)],
            'thumbnail'    => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024)],
        ]);

        // ── Unique slug ───────────────────────────────────────────────────────
        $slug       = Str::slug($request->title);
        $uniqueSlug = $slug;
        $i = 1;
        while (NewReport::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $slug . '-' . $i++;
        }

        // ── PDF → private local disk ──────────────────────────────────────────
        $pdfName  = $uniqueSlug . '-' . now()->format('YmdHis') . '.pdf';
        $filePath = $request->file('pdf_file')->storeAs('reports', $pdfName, 'local');

        // ── Thumbnail → public/images/reports/ (optional) ─────────────────────
        $imagePath = null;
        if ($request->hasFile('thumbnail')) {
            if (!is_dir(public_path('images/reports'))) {
                mkdir(public_path('images/reports'), 0755, true);
            }
            $ext       = $request->file('thumbnail')->getClientOriginalExtension();
            $imgName   = $uniqueSlug . '-thumb-' . now()->format('YmdHis') . '.' . $ext;
            $request->file('thumbnail')->move(public_path('images/reports'), $imgName);
            $imagePath = 'images/reports/' . $imgName;
        }

        NewReport::create([
            'title'        => trim($request->title),
            'slug'         => $uniqueSlug,
            'period'       => trim($request->period ?? ''),
            'description'  => trim($request->description ?? ''),
            'file_path'    => $filePath,
            'image_path'   => $imagePath,
            'min_tier'     => (int) $request->min_tier,
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()
            ->route('admin.reports.index')
            ->with('success', 'Report "' . trim($request->title) . '" uploaded successfully.');
    }

    // =========================================================================
    // EDIT
    // =========================================================================

    public function edit(NewReport $report)
    {
        return view('admin.reports.edit', compact('report'));
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, NewReport $report)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'period'           => 'nullable|string|max:100',
            'description'      => 'nullable|string|max:2000',
            'min_tier'         => 'required|integer|in:1,2,3',
            'is_published'     => 'nullable|boolean',
            'pdf_file'         => ['nullable', File::types(['pdf'])->max(50 * 1024)],
            'thumbnail'        => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024)],
            'remove_thumbnail' => 'nullable|boolean',
        ]);

        $data = [
            'title'        => trim($request->title),
            'period'       => trim($request->period ?? ''),
            'description'  => trim($request->description ?? ''),
            'min_tier'     => (int) $request->min_tier,
            'is_published' => $request->boolean('is_published', true),
        ];

        // Re-slug only when title changed
        if (trim($request->title) !== $report->title) {
            $slug       = Str::slug($request->title);
            $uniqueSlug = $slug;
            $i = 1;
            while (NewReport::where('slug', $uniqueSlug)->where('id', '!=', $report->id)->exists()) {
                $uniqueSlug = $slug . '-' . $i++;
            }
            $data['slug'] = $uniqueSlug;
        }

        $slug = $data['slug'] ?? $report->slug;

        // ── Replace PDF if a new one was uploaded ─────────────────────────────
        if ($request->hasFile('pdf_file')) {
            Storage::disk('local')->delete($report->file_path);
            $pdfName           = $slug . '-' . now()->format('YmdHis') . '.pdf';
            $data['file_path'] = $request->file('pdf_file')->storeAs('reports', $pdfName, 'local');
        }

        // ── Replace thumbnail ─────────────────────────────────────────────────
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail from public folder
            if ($report->image_path && file_exists(public_path($report->image_path))) {
                unlink(public_path($report->image_path));
            }
            // Ensure directory exists
            if (!is_dir(public_path('images/reports'))) {
                mkdir(public_path('images/reports'), 0755, true);
            }
            $ext                = $request->file('thumbnail')->getClientOriginalExtension();
            $imgName            = $slug . '-thumb-' . now()->format('YmdHis') . '.' . $ext;
            $request->file('thumbnail')->move(public_path('images/reports'), $imgName);
            $data['image_path'] = 'images/reports/' . $imgName;

            // ── Remove thumbnail without replacement ──────────────────────────────
        } elseif ($request->boolean('remove_thumbnail') && $report->image_path) {
            if (file_exists(public_path($report->image_path))) {
                unlink(public_path($report->image_path));
            }
            $data['image_path'] = null;
        }

        $report->update($data);

        return redirect()
            ->route('admin.reports.index')
            ->with('success', 'Report updated successfully.');
    }

    // =========================================================================
    // TOGGLE PUBLISH — JSON endpoint (used by inline toggle on index page)
    // =========================================================================

    public function togglePublish(NewReport $report)
    {
        $report->update(['is_published' => !$report->is_published]);

        return response()->json([
            'success'      => true,
            'is_published' => $report->is_published,
            'message'      => $report->is_published ? 'Report published.' : 'Report unpublished.',
        ]);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(NewReport $report)
    {
        // Delete PDF from private disk
        Storage::disk('local')->delete($report->file_path);

        // Delete thumbnail from public folder
        if ($report->image_path && file_exists(public_path($report->image_path))) {
            unlink(public_path($report->image_path));
        }

        $title = $report->title;
        $report->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => "\"$title\" deleted."]);
        }

        return redirect()
            ->route('admin.reports.index')
            ->with('success', "\"$title\" deleted successfully.");
    }
}
