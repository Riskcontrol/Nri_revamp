<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;          // FIX 1: was missing — caused fatal "Class not found"
use App\Services\SpreadsheetProcessorService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;

class FileProcessorController extends Controller
{
    private SpreadsheetProcessorService $processorService;

    public function __construct(SpreadsheetProcessorService $processorService)
    {
        $this->processorService = $processorService;
    }

    /**
     * Show the AI file processor upload form.
     */
    public function index()
    {
        return view('admin.file-processor.index');
    }

    /**
     * Accept an uploaded Excel/CSV file, enrich every row with AI-generated
     * business-report fields via GroqAIService, and return the enriched
     * spreadsheet as an .xlsx download.
     *
     * BUGS FIXED vs the original:
     *
     * 1. Missing `use App\Http\Controllers\Controller` — the controller is in
     *    the Admin sub-namespace so PHP could not resolve the bare `Controller`
     *    parent class, causing a fatal "Class not found" error on every request.
     *
     * 2. Dead code / undefined variable after the download return:
     *       return response()->download(...)->deleteFileAfterSend(true);
     *       return $response->withCookie(...);   ← unreachable, $response undefined
     *    The second return statement was never reached AND would have thrown
     *    "Undefined variable $response" if it somehow were.  Removed entirely.
     *
     * 3. Output filename always forced the original extension (e.g. .csv) even
     *    though PhpSpreadsheet's IOFactory::createWriter writes an Xlsx binary.
     *    Sending xlsx bytes under a .csv name causes Excel / LibreOffice to
     *    refuse the file or show a format error.  The output filename is now
     *    always .xlsx regardless of what the user uploaded.
     */
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_file' => 'required|file|mimes:xlsx,csv,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please upload a valid Excel or CSV file (max 10 MB).');
        }

        try {
            $file     = $request->file('data_file');
            $filePath = $file->getRealPath();

            // Run the AI enrichment pass over every row
            $processedSpreadsheet = $this->processorService->processFile($filePath);

            // FIX 3: output is ALWAYS an xlsx binary, so the download filename
            // must always carry the .xlsx extension — regardless of what the
            // user originally uploaded (.csv, .xls, etc.).
            $baseName    = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = $baseName . '-ai-processed.xlsx';

            // Write to a temp file and stream it as a download
            $tempFile = sys_get_temp_dir() . '/' . uniqid('nri_', true) . '_' . $newFilename;
            $writer   = IOFactory::createWriter($processedSpreadsheet, 'Xlsx');
            $writer->save($tempFile);

            // FIX 2: single clean return — the old second `return $response->…`
            // was unreachable dead code referencing an undefined $response variable.
            return response()
                ->download($tempFile, $newFilename)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()
                ->with('error', 'AI processing failed: ' . $e->getMessage())
                ->withInput();
        }
    }
}
