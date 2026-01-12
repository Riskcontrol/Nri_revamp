<?php

namespace App\Http\Controllers;

use App\Services\SpreadsheetProcessorService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;

class FileProcessorController extends Controller
{
    private SpreadsheetProcessorService $processorService;

    public function __construct(SpreadsheetProcessorService $processorService)
    {
        $this->processorService = $processorService;
    }

    /**
     * Show the file upload form
     */
    public function index()
    {
        return view('file-processor.index');
    }

    /**
     * Process the uploaded file
     */
    public function process(Request $request)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'data_file' => 'required|file|mimes:xlsx,csv,xls|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please upload a valid Excel or CSV file (max 10MB)');
        }

        try {
            $file = $request->file('data_file');
            $filePath = $file->getRealPath();

            // Process the file
            $processedSpreadsheet = $this->processorService->processFile($filePath);

            // Generate output filename
            $originalName = $file->getClientOriginalName();
            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $newFilename = $filename . '-ai-processed.' . $extension;

            // Save to temp file
            $tempFile = sys_get_temp_dir() . '/' . uniqid() . '_' . $newFilename;
            $writer = IOFactory::createWriter($processedSpreadsheet, 'Xlsx');
            $writer->save($tempFile);

            // Return download response with cookie to signal completion

            return response()
                ->download($tempFile, $newFilename)
                ->deleteFileAfterSend(true);
            return $response->withCookie(cookie('downloadComplete', '1', 1, '/', null, false, false));
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to process file: ' . $e->getMessage())
                ->withInput();
        }
    }
}
