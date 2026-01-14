<?php

namespace App\Http\Controllers;

use App\Models\DataImport;
use App\Models\tbldataentry;
use App\Models\StateNeighbourhoods;
use App\Models\tbllga;
use App\Models\tblriskfactors;
use App\Models\tblriskindicators;
use App\Models\Motive;
use App\Models\MotivesSpecific;
use App\Models\AttackGroup;
use App\Models\AttackSubGroup;
use App\Models\TargetType;
use App\Models\TargetSubType;
use App\Models\WeaponType;
use App\Models\WeaponSubType;
use App\Models\DayPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class DataImportController extends Controller
{
    public function index()
    {
        $recentImports = DataImport::orderBy('created_at', 'desc')->paginate(20);
        return view('dataanalyst.data-upload', compact('recentImports'));
    }

    public function import(Request $request)
    {
        Log::info('=== IMPORT STARTED ===');

        $request->validate([
            'incident_file' => 'required|file|mimes:xlsx,xls,xlsm,xlsb|max:10240',
        ]);

        $startTime = microtime(true);
        $file = $request->file('incident_file');
        $fileName = $file->getClientOriginalName();

        Log::info('File received', ['name' => $fileName]);

        $import = DataImport::create([
            'sheet_name' => $fileName,
            'user_id' => auth()->id(),
            'status' => 'processing',
            'rows_inserted' => 0,
            'rows_failed' => 0,
            'total_rows' => 0,
            'failed_rows' => json_encode([]), // Store as JSON string initially
        ]);

        Log::info('Import record created', ['id' => $import->id]);

        DB::beginTransaction();

        try {
            $theCollection = Excel::toArray(collect([]), $file);

            if (empty($theCollection) || empty($theCollection[0])) {
                throw new \Exception('Empty or invalid Excel file');
            }

            $headers = $theCollection[0][0];
            $rows = $theCollection[0];

            Log::info('Excel parsed', [
                'total_rows' => count($rows),
                'headers' => $headers
            ]);

            $processedData = $this->processExcelRows($rows, $headers);
            Log::info('Rows processed', ['count' => count($processedData)]);

            $import->update(['total_rows' => count($processedData)]);

            $result = $this->importIncidents($processedData, $import);

            Log::info('Import completed', [
                'success' => $result['success_count'],
                'failed' => $result['failed_count']
            ]);

            $processingTime = round(microtime(true) - $startTime, 2);

            $import->update([
                'rows_inserted' => $result['success_count'],
                'rows_failed' => $result['failed_count'],
                'failed_rows' => json_encode($result['errors']),
                'processing_time' => $processingTime,
                'status' => 'completed',
            ]);

            DB::commit();

            return redirect()->route('data.import.show', $import->id)
                ->with('successAlert', "Import completed: {$result['success_count']} succeeded, {$result['failed_count']} failed.");
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Import failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $import->update([
                'status' => 'failed',
                'failed_rows' => json_encode([['error' => $e->getMessage()]])
            ]);

            return redirect()->route('data.import.index')
                ->with('errorAlert', 'Import failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $import = DataImport::findOrFail($id);

        $failedRows = json_decode($import->failed_rows, true);
        if (!is_array($failedRows)) {
            $failedRows = [];
        }

        // Query by import_id for reliable results
        $importedIncidents = DB::table('tbldataentry')
            ->where('import_id', $import->id)
            ->orderBy('eventid', 'desc')
            ->paginate(50);

        return view('dataanalyst.import-details', compact('import', 'failedRows', 'importedIncidents'));
    }

    private function processExcelRows($rows, $headers)
    {
        $processedData = [];
        $emptyRowCount = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

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

    private function importIncidents($dataRows, $import)
    {
        Log::info('Starting incident import', ['total' => count($dataRows)]);

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($dataRows as $data) {
            try {
                $error = ['row_num' => $data['row_num']];
                $hasError = false;

                // Date
                $date = null;
                if (!empty($data['year']) && !empty($data['month']) && !empty($data['day'])) {
                    try {
                        $date = Carbon::createFromFormat('d M Y', $data['day'] . ' ' . $data['month'] . ' ' . $data['year']);
                    } catch (\Exception $e) {
                        $error['date'] = 'Invalid date format';
                        $hasError = true;
                    }
                } else {
                    $error['date'] = 'Date is required';
                    $hasError = true;
                }

                // State
                $lat_long = null;
                if (!empty($data['state'])) {
                    $lat_long = tbllga::where('State', 'LIKE', '%' . $data['state'] . '%')->first();
                    if (!$lat_long) {
                        $error['state'] = 'Invalid state';
                        $hasError = true;
                    }
                } else {
                    $error['state'] = 'State is required';
                    $hasError = true;
                }

                // Risk Factor
                $riskfactorName = null;
                if (!empty($data['risk_factor'])) {
                    $normalized = str_replace('threats', '', strtolower($data['risk_factor']));
                    $riskfactor = tblriskfactors::where('name', 'LIKE', '%' . $normalized . '%')->first();
                    if (!$riskfactor) {
                        $error['risk_factor'] = 'Invalid risk factor';
                        $hasError = true;
                    } else {
                        $riskfactorName = $riskfactor->name;
                    }
                } else {
                    $error['risk_factor'] = 'Risk factor is required';
                    $hasError = true;
                }

                // Risk Indicator
                $riskindicatorValue = null;
                if (!empty($data['risk_indicator']) && $riskfactorName) {
                    $riskindicator = tblriskindicators::where('indicators', $data['risk_indicator'])
                        ->where('factors', $riskfactorName)
                        ->first();
                    if (!$riskindicator) {
                        $error['risk_indicator'] = 'Invalid risk indicator';
                        $hasError = true;
                    } else {
                        $riskindicatorValue = $riskindicator->indicators;
                    }
                } else if (empty($data['risk_indicator'])) {
                    $error['risk_indicator'] = 'Risk indicator is required';
                    $hasError = true;
                }

                // Impact
                $impact_value = '';
                if (!empty($data['impact'])) {
                    $impacts = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];
                    foreach ($impacts as $key => $val) {
                        if (str_contains(strtolower($data['impact']), $key)) {
                            $impact_value = $val;
                            break;
                        }
                    }
                    if (!$impact_value) {
                        $error['impact'] = 'Invalid impact';
                        $hasError = true;
                    }
                } else {
                    $error['impact'] = 'Impact is required';
                    $hasError = true;
                }

                // Caption
                if (empty($data['caption'])) {
                    $error['caption'] = 'Caption is required';
                    $hasError = true;
                }

                if ($hasError) {
                    $failedCount++;
                    $errors[] = $error;
                    Log::warning('Row validation failed', $error);
                    continue;
                }

                // Insert to database
                $unique_code = Carbon::now()->format('YmdHisu');

                $insertData = [
                    'import_id' => $import->id, // ADD THIS LINE
                    'eventid' => $unique_code,
                    'location' => $lat_long->State,
                    'lga' => $lat_long->LGA ?? '',
                    'eventdate' => $date->format('m/d/Y'),
                    'eventdateToUse' => $date->format('Y-m-d'),
                    'dd' => $date->format('d'),
                    'eventday' => $date->format('d'),
                    'week' => $date->format('W'),
                    'mm' => $date->format('m'),
                    'eventmonth' => $date->format('m'),
                    'YY' => $date->format('Y'),
                    'eventyear' => $date->format('Y'),
                    'riskfactors' => $riskfactorName,
                    'riskindicators' => $riskindicatorValue,
                    'impact' => $impact_value,
                    'caption' => $data['caption'],
                    'author' => auth()->user()->email ?? 'system',
                    'datecreated' => Carbon::now()->format('M d, Y'),
                    'auditauthor' => auth()->user()->email ?? 'system',
                    'auditdatecreated' => Carbon::now()->format('M d, Y'),
                    'audittimecreated' => Carbon::now()->format('h:i:s a'),
                    'latitude' => $data['latitude'] ?? '',
                    'longitude' => $data['longitude'] ?? '',
                    'Casualties_count' => !empty($data['deaths_count']) ? (int)$data['deaths_count'] : null,
                    'Injuries_count' => !empty($data['injuries_count']) ? (int)$data['injuries_count'] : null,
                    'victim' => $data['victim'] ?? null,
                ];

                DB::table('tbldataentry')->insert($insertData);

                $successCount++;
                Log::info('Row inserted', ['row' => $data['row_num'], 'eventid' => $unique_code]);
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'row_num' => $data['row_num'] ?? 'N/A',
                    'error' => $e->getMessage()
                ];
                Log::error('Row insert failed', [
                    'row' => $data['row_num'] ?? 'N/A',
                    'error' => $e->getMessage(),
                    'line' => $e->getLine()
                ]);
            }
        }

        return [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors
        ];
    }

    public function downloadFailedRows($id)
    {
        $import = DataImport::findOrFail($id);
        $failedRows = json_decode($import->failed_rows, true) ?? [];

        $filename = "failed_rows_{$id}.csv";
        $handle = fopen('php://temp', 'w');

        fputcsv($handle, ['Row Number', 'Field', 'Error Message']);

        foreach ($failedRows as $row) {
            $rowNum = $row['row_num'] ?? 'N/A';
            foreach ($row as $field => $error) {
                if ($field === 'row_num') continue;
                fputcsv($handle, [$rowNum, ucfirst(str_replace('_', ' ', $field)), $error]);
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function exportImportedIncidents($id)
    {
        $import = DataImport::findOrFail($id);

        // Query by import_id for reliable results
        $incidents = DB::table('tbldataentry')
            ->where('import_id', $import->id)
            ->get();

        if ($incidents->isEmpty()) {
            return redirect()->back()->with('errorAlert', 'No incidents found to export');
        }

        $filename = "imported_incidents_{$id}.csv";
        $handle = fopen('php://temp', 'w');

        $columns = array_keys((array)$incidents->first());
        fputcsv($handle, $columns);

        foreach ($incidents as $incident) {
            fputcsv($handle, (array)$incident);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
