<?php

namespace App\Http\Controllers;

use App\Models\DataImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class DataImportController extends Controller
{
    /**
     * Display upload form and recent imports
     */
    public function index()
    {
        $recentImports = DataImport::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('dataanalyst.data-upload', compact('recentImports'));
    }

    /**
     * Handle file import
     */
    public function import(Request $request)
    {
        // Validate the request
        $request->validate([
            'incident_file' => 'required|file|mimes:xlsx,xls,xlsm,xlsb|max:10240',
        ]);

        $startTime = microtime(true);
        $file = $request->file('incident_file');
        $fileName = $file->getClientOriginalName();

        // Create import record
        $import = DataImport::create([
            'sheet_name' => $fileName,
            'user_id' => auth()->id(),
            'status' => 'processing',
            'rows_inserted' => 0,
            'rows_failed' => 0,
            'total_rows' => 0,
            'failed_rows' => [],
        ]);

        DB::beginTransaction();

        try {
            // Process the Excel file
            $theCollection = Excel::toArray(collect([]), $file);

            if (empty($theCollection) || empty($theCollection[0])) {
                throw new \Exception('Empty or invalid Excel file');
            }

            $headers = $theCollection[0][0];
            $rows = $theCollection[0];

            // Process rows
            $processedData = $this->processExcelRows($rows, $headers);
            $import->update(['total_rows' => count($processedData)]);

            // Import the data using your existing logic
            $result = $this->importIncidents($processedData, $import);

            // Calculate processing time
            $processingTime = round(microtime(true) - $startTime, 2);

            // Update import record
            $import->update([
                'rows_inserted' => $result['success_count'],
                'rows_failed' => $result['failed_count'],
                'failed_rows' => json_encode($result['errors']),
                'processing_time' => $processingTime,
                'status' => 'completed',
            ]);

            DB::commit();

            return redirect()->route('data.import.show', $import->id)
                ->with('successAlert', "Import completed: {$result['success_count']} succeeded, {$result['failed_count']} failed out of " . count($processedData) . " total rows.");
        } catch (\Exception $e) {
            DB::rollBack();

            $import->update([
                'status' => 'failed',
                'failed_rows' => json_encode([['error' => $e->getMessage()]])
            ]);

            Log::error('Import failed', [
                'import_id' => $import->id,
                'file' => $fileName,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('data.import.index')
                ->with('errorAlert', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show detailed import information
     */
    public function show($id)
    {
        $import = DataImport::with('user')->findOrFail($id);
        $failedRows = $import->failed_rows ?? [];

        // Get imported incidents
        $importedIncidents = DB::table('tbldataentry')
            ->whereDate('auditdatecreated', $import->created_at->toDateString())
            ->orderBy('eventid', 'desc')
            ->paginate(50);

        return view('dataanalyst.import-details', compact('import', 'failedRows', 'importedIncidents'));
    }

    /**
     * Process Excel rows into structured array
     */
    private function processExcelRows($rows, $headers)
    {
        $processedData = [];
        $emptyRowCount = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header

            // Check for empty rows
            if (empty(array_filter($row, fn($cell) => !is_null($cell) && trim($cell) !== ''))) {
                $emptyRowCount++;
                if ($emptyRowCount >= 3) break;
                continue;
            }

            $emptyRowCount = 0;
            $rowData = ['row_num' => $index + 1];

            foreach ($headers as $colIndex => $header) {
                $value = $row[$colIndex] ?? '';
                $cleanValue = is_string($value) ? trim(strip_tags($value)) : $value;
                $rowData[strtolower($header)] = $cleanValue;
            }

            $processedData[] = $rowData;
        }

        return $processedData;
    }

    /**
     * Import incidents - use your existing add_incidents_file logic here
     */
    private function importIncidents($dataRows, $import)
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        // You can copy your existing add_incidents_file logic here
        // For now, I'll provide a simplified structure

        foreach ($dataRows as $row) {
            try {
                // Your existing validation and insertion logic
                // If successful:
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'row_num' => $row['row_num'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors
        ];
    }

    /**
     * Download failed rows as Excel
     */
    public function downloadFailedRows($id)
    {
        $import = DataImport::findOrFail($id);
        $failedRows = $import->failed_rows ?? [];

        // Create a simple CSV for now
        $filename = "failed_rows_{$id}.csv";
        $handle = fopen('php://temp', 'w');

        // Write headers
        fputcsv($handle, ['Row Number', 'Errors']);

        // Write data
        foreach ($failedRows as $row) {
            $errors = collect($row)->except('row_num')->map(function ($v, $k) {
                return "$k: $v";
            })->implode('; ');

            fputcsv($handle, [$row['row_num'] ?? 'N/A', $errors]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export imported incidents
     */
    public function exportImportedIncidents($id)
    {
        $import = DataImport::findOrFail($id);

        $incidents = DB::table('tbldataentry')
            ->whereDate('auditdatecreated', $import->created_at->toDateString())
            ->get();

        $filename = "imported_incidents_{$id}.csv";
        $handle = fopen('php://temp', 'w');

        // Get column names from first record
        if ($incidents->count() > 0) {
            $columns = array_keys((array)$incidents->first());
            fputcsv($handle, $columns);

            foreach ($incidents as $incident) {
                fputcsv($handle, (array)$incident);
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
