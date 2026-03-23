<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataImport;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class DataImportController extends Controller
{
    // =========================================================================
    // INDEX — upload form + history listing
    // =========================================================================

    public function index()
    {
        $recentImports = DataImport::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.data-import.index', compact('recentImports'));
    }

    // =========================================================================
    // IMPORT — process an uploaded Excel file
    // =========================================================================

    public function import(Request $request)
    {
        Log::info('[DataImport] Import started', ['user' => auth()->id()]);

        $request->validate([
            'incident_file' => 'required|file|mimes:xlsx,xls,xlsm,xlsb|max:10240',
        ]);

        $startTime = microtime(true);
        $file      = $request->file('incident_file');
        $fileName  = $file->getClientOriginalName();

        // ── Create import tracking record inside a transaction so the history
        //    record and its data rows always succeed or fail together.
        $import = DB::transaction(function () use ($fileName) {
            return DataImport::create([
                'sheet_name'    => $fileName,
                'user_id'       => auth()->id(),
                'status'        => 'processing',
                'rows_inserted' => 0,
                'rows_failed'   => 0,
                'total_rows'    => 0,
                'failed_rows'   => '[]',
            ]);
        });

        if (! $import || ! $import->id) {
            return redirect()->route('admin.data-import.index')
                ->with('errorAlert', 'Could not create import record. Please try again.');
        }

        Log::info('[DataImport] Tracking record created', ['import_id' => $import->id]);

        try {
            $theCollection = Excel::toArray(collect([]), $file);

            if (empty($theCollection) || empty($theCollection[0])) {
                throw new \Exception('Uploaded file is empty or unreadable.');
            }

            $allRows  = $theCollection[0];
            $headers  = $allRows[0];
            $dataRows = $this->parseRows($allRows, $headers);

            Log::info('[DataImport] Rows parsed', ['count' => count($dataRows)]);

            $import->update(['total_rows' => count($dataRows)]);

            $result = $this->processRows($dataRows, $import);

            $processingTime = round(microtime(true) - $startTime, 2);

            $import->update([
                'rows_inserted'   => $result['success_count'],
                'rows_failed'     => $result['failed_count'],
                'failed_rows'     => json_encode($result['errors']),
                'processing_time' => $processingTime,
                'status'          => 'completed',
            ]);

            $this->bustRiskCaches($result['affected_years']);

            Log::info('[DataImport] Import completed', [
                'success' => $result['success_count'],
                'failed'  => $result['failed_count'],
                'time'    => $processingTime . 's',
            ]);

            return redirect()->route('admin.data-import.show', $import->id)
                ->with('successAlert', "Import completed: {$result['success_count']} rows inserted, {$result['failed_count']} failed.");
        } catch (\Exception $e) {
            Log::error('[DataImport] Import failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            $import->update([
                'status'      => 'failed',
                'failed_rows' => json_encode([['error' => $e->getMessage()]]),
            ]);

            return redirect()->route('admin.data-import.index')
                ->with('errorAlert', 'Import failed: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // SHOW — single import detail page
    // =========================================================================

    public function show($id)
    {
        $import = DataImport::with('user')->findOrFail($id);

        $failedRows = json_decode($import->failed_rows ?? '[]', true);
        if (! is_array($failedRows)) {
            $failedRows = [];
        }

        $importedIncidents = DB::table('tbldataentry')
            ->where('import_id', $import->id)
            ->orderBy('id', 'desc')
            ->paginate(50);

        // Count weekly rows for this import (for delete impact messaging)
        $weeklyCount = DB::table('tblweeklydataentry')
            ->where('import_id', $import->id)
            ->count();

        return view('admin.data-import.show', compact(
            'import',
            'failedRows',
            'importedIncidents',
            'weeklyCount'
        ));
    }

    // =========================================================================
    // DELETE DATA — remove all incident rows for this import (keeps history)
    // =========================================================================

    public function deleteData($id)
    {
        $import = DataImport::findOrFail($id);

        DB::transaction(function () use ($import) {
            $deleted = DB::table('tbldataentry')->where('import_id', $import->id)->delete();
            DB::table('tblweeklydataentry')->where('import_id', $import->id)->delete();

            Log::info('[DataImport] Data deleted', [
                'import_id'       => $import->id,
                'rows_deleted'    => $deleted,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'All incident data for this import has been deleted.',
        ]);
    }

    // =========================================================================
    // DELETE IMPORT — remove history record AND all data rows
    // =========================================================================

    public function destroyImport($id)
    {
        $import = DataImport::findOrFail($id);

        DB::transaction(function () use ($import) {
            DB::table('tblweeklydataentry')->where('import_id', $import->id)->delete();
            DB::table('tbldataentry')->where('import_id', $import->id)->delete();
            $import->delete();

            Log::info('[DataImport] Import + data fully deleted', ['import_id' => $import->id]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Import history and all associated data have been permanently deleted.',
        ]);
    }

    // =========================================================================
    // DELETE SINGLE INCIDENT ROW
    // =========================================================================

    public function deleteIncident(Request $request, $importId)
    {
        $eventid = $request->input('eventid');

        if (! $eventid) {
            return response()->json(['success' => false, 'message' => 'Event ID is required.'], 422);
        }

        DB::transaction(function () use ($eventid) {
            DB::table('tbldataentry')->where('eventid', $eventid)->delete();
            DB::table('tblweeklydataentry')->where('eventid', $eventid)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Incident row deleted from both tables.',
        ]);
    }

    // =========================================================================
    // DOWNLOAD HELPERS
    // =========================================================================

    public function downloadFailedRows($id)
    {
        $import     = DataImport::findOrFail($id);
        $failedRows = json_decode($import->failed_rows ?? '[]', true) ?? [];

        $handle = fopen('php://temp', 'w');
        fputcsv($handle, ['Row Number', 'Field', 'Error Message']);

        foreach ($failedRows as $row) {
            $rowNum = $row['row_num'] ?? 'N/A';
            foreach ($row as $field => $message) {
                if ($field === 'row_num') continue;
                fputcsv($handle, [$rowNum, ucfirst(str_replace('_', ' ', $field)), $message]);
            }
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"failed_rows_{$id}.csv\"");
    }

    public function exportImportedIncidents($id)
    {
        $import    = DataImport::findOrFail($id);
        $incidents = DB::table('tbldataentry')->where('import_id', $import->id)->get();

        if ($incidents->isEmpty()) {
            return redirect()->back()->with('errorAlert', 'No incidents found for this import.');
        }

        $handle  = fopen('php://temp', 'w');
        $columns = array_keys((array) $incidents->first());
        fputcsv($handle, $columns);
        foreach ($incidents as $incident) {
            fputcsv($handle, (array) $incident);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"imported_incidents_{$id}.csv\"");
    }

    // =========================================================================
    // PRIVATE — row parsing
    // =========================================================================

    private function parseRows(array $rows, array $headers): array
    {
        $parsed      = [];
        $emptyStreak = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            $isEmpty = empty(array_filter($row, fn($cell) => ! is_null($cell) && trim((string) $cell) !== ''));
            if ($isEmpty) {
                if (++$emptyStreak >= 3) break;
                continue;
            }
            $emptyStreak = 0;

            $rowData = ['row_num' => $index + 1];
            foreach ($headers as $colIndex => $header) {
                $raw   = $row[$colIndex] ?? '';
                $clean = is_string($raw)
                    ? filter_var(trim($raw), FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)
                    : $raw;
                $rowData[strtolower((string) $header)] = $clean;
            }

            $parsed[] = $rowData;
        }

        return $parsed;
    }

    // =========================================================================
    // PRIVATE — row processing loop
    // =========================================================================

    private function processRows(array $dataRows, DataImport $import): array
    {
        $successCount  = 0;
        $failedCount   = 0;
        $errors        = [];
        $affectedYears = [];

        foreach ($dataRows as $data) {
            try {
                $result = $this->insertRow($data, $import->id);

                if ($result['success']) {
                    $successCount++;
                    if (! empty($result['year'])) {
                        $affectedYears[] = $result['year'];
                    }
                } else {
                    $failedCount++;
                    $errors[] = $result['error'];
                    Log::warning('[DataImport] Row validation failed', $result['error']);
                }
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'row_num' => $data['row_num'] ?? 'N/A',
                    'error'   => $e->getMessage(),
                ];
                Log::error('[DataImport] Row exception', [
                    'row'   => $data['row_num'] ?? 'N/A',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success_count'  => $successCount,
            'failed_count'   => $failedCount,
            'errors'         => $errors,
            'affected_years' => array_unique($affectedYears),
        ];
    }

    // =========================================================================
    // PRIVATE — single row insert (both tables, same import_id)
    // =========================================================================

    private function insertRow(array $data, int $importId): array
    {
        $error    = ['row_num' => $data['row_num']];
        $hasError = false;

        // ── 1. DATE ──────────────────────────────────────────────────────────
        $date = null;
        if (! empty($data['year']) && ! empty($data['month']) && ! empty($data['day'])) {
            try {
                $date = Carbon::createFromFormat(
                    'd M Y',
                    $data['day'] . ' ' . $data['month'] . ' ' . $data['year']
                );
            } catch (\Exception $e) {
                $error['date'] = 'Invalid date — expected day/month/year columns';
                $hasError = true;
            }
        } else {
            $error['date'] = 'Date columns (day, month, year) are required';
            $hasError = true;
        }

        // ── 2. STATE + LGA ────────────────────────────────────────────────────
        $latLong   = null;
        $lgaRecord = null;

        if (! empty($data['state'])) {
            if (! empty($data['lga'])) {
                $lgaRecord = tbllga::where('State', 'LIKE', '%' . $data['state'] . '%')
                    ->where('LGA', 'LIKE', '%' . $data['lga'] . '%')
                    ->first();

                if (! $lgaRecord) {
                    tbllga::insert([
                        'LGA'       => $data['lga'],
                        'State'     => $data['state'],
                        'latitude'  => $data['latitude']  ?? '',
                        'longitude' => $data['longitude'] ?? '',
                        'author'    => auth()->user()->email ?? 'import',
                    ]);
                    $lgaRecord = tbllga::where('State', 'LIKE', '%' . $data['state'] . '%')
                        ->where('LGA', 'LIKE', '%' . $data['lga'] . '%')
                        ->first();
                }
                if (! $lgaRecord) {
                    $lgaRecord = tbllga::where('State', 'LIKE', '%' . $data['state'] . '%')->first();
                }
            } else {
                $lgaRecord = tbllga::where('State', $data['state'])->first();
            }

            if (! $lgaRecord) {
                $error['state'] = 'Invalid or unrecognised state: ' . $data['state'];
                $hasError = true;
            } else {
                $latLong = $lgaRecord;
            }
        } else {
            $error['state'] = 'State is required';
            $hasError = true;
        }

        // ── 3. NEIGHBOURHOOD ─────────────────────────────────────────────────
        $neighbourhood = null;
        if (! empty($data['neighbourhood']) && $lgaRecord) {
            $neighbourhood = StateNeighbourhoods::select('state_neighbourhoods.*', 'tbllga.LGA as lga_name')
                ->leftJoin('tbllga', 'tbllga.ID', 'state_neighbourhoods.state_lga_id')
                ->where(DB::raw('lower(neighbourhood_name)'), strtolower(trim($data['neighbourhood'])))
                ->first();

            if (! $neighbourhood) {
                StateNeighbourhoods::insert([
                    'state_lga_id'       => $lgaRecord->ID ?? 0,
                    'state'              => $data['state'],
                    'neighbourhood_name' => $data['neighbourhood'],
                    'latitude'           => $data['latitude']  ?? '',
                    'longitude'          => $data['longitude'] ?? '',
                ]);
                $neighbourhood = StateNeighbourhoods::select('state_neighbourhoods.*', 'tbllga.LGA as lga_name')
                    ->leftJoin('tbllga', 'tbllga.ID', 'state_neighbourhoods.state_lga_id')
                    ->where(DB::raw('lower(neighbourhood_name)'), strtolower(trim($data['neighbourhood'])))
                    ->first();
            }
        }

        // ── 4. RISK FACTOR ────────────────────────────────────────────────────
        $riskfactorName = null;
        if (! empty($data['risk_factor'])) {
            $normalized = str_replace('threats', '', strtolower($data['risk_factor']));
            $riskfactor = tblriskfactors::where('name', 'LIKE', '%' . $normalized . '%')->first();
            if (! $riskfactor) {
                $error['risk_factor'] = 'Invalid risk factor: ' . $data['risk_factor'];
                $hasError = true;
            } else {
                $riskfactorName = $riskfactor->name;
            }
        } else {
            $error['risk_factor'] = 'Risk factor is required';
            $hasError = true;
        }

        // ── 5. RISK INDICATOR ─────────────────────────────────────────────────
        $riskindicatorValue = null;
        if (! empty($data['risk_indicator'])) {
            if ($riskfactorName) {
                $riskindicator = tblriskindicators::where('indicators', $data['risk_indicator'])
                    ->where('factors', $riskfactorName)
                    ->first();
                if (! $riskindicator) {
                    $error['risk_indicator'] = "Invalid indicator \"{$data['risk_indicator']}\" for \"{$riskfactorName}\"";
                    $hasError = true;
                } else {
                    $riskindicatorValue = $riskindicator->indicators;
                }
            } else {
                $error['risk_indicator'] = 'Cannot validate indicator without valid risk factor';
                $hasError = true;
            }
        } else {
            $error['risk_indicator'] = 'Risk indicator is required';
            $hasError = true;
        }

        // ── 6. IMPACT ─────────────────────────────────────────────────────────
        $impactValue = '';
        if (! empty($data['impact'])) {
            foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $key => $val) {
                if (str_contains(strtolower(trim($data['impact'])), $key)) {
                    $impactValue = $val;
                    break;
                }
            }
            if (! $impactValue) {
                $error['impact'] = 'Invalid impact value (expected Low, Medium, or High)';
                $hasError = true;
            }
        } else {
            $error['impact'] = 'Impact is required';
            $hasError = true;
        }

        // ── 7. CAPTION ────────────────────────────────────────────────────────
        if (empty($data['caption'])) {
            $error['caption'] = 'Caption is required';
            $hasError = true;
        }

        if ($hasError) {
            return ['success' => false, 'error' => $error];
        }

        // ── 8. OPTIONAL LOOKUPS ───────────────────────────────────────────────
        $dayPeriodId = 0;
        if (! empty($data['day_period'])) {
            $dp          = DayPeriod::where('name', 'LIKE', '%' . $data['day_period'] . '%')->first();
            $dayPeriodId = $dp->id ?? 0;
        }

        $targetTypeId = $targetSubtypeId = null;
        if (! empty($data['target_subtype'])) {
            $tSub = TargetSubType::where('name', 'LIKE', '%' . trim($data['target_subtype']) . '%')->first();
            if ($tSub) {
                $targetSubtypeId = $tSub->id;
                $targetTypeId    = $tSub->target_type_id;
            } else {
                if (! empty($data['target_type'])) {
                    $tType        = TargetType::firstOrCreate(['name' => trim($data['target_type'])]);
                    $targetTypeId = $tType->id;
                }
                if ($targetTypeId) {
                    $newSub          = TargetSubType::create(['name' => trim($data['target_subtype']), 'target_type_id' => $targetTypeId]);
                    $targetSubtypeId = $newSub->id;
                }
            }
        } elseif (! empty($data['target_type'])) {
            $tType        = TargetType::firstOrCreate(['name' => trim($data['target_type'])]);
            $targetTypeId = $tType->id;
        }

        $weaponTypeId = $weaponSubtypeId = null;
        if (! empty($data['weapon_subtype'])) {
            $wSub = WeaponSubType::where('name', 'LIKE', '%' . trim($data['weapon_subtype']) . '%')->first();
            if ($wSub) {
                $weaponSubtypeId = $wSub->id;
                $weaponTypeId    = $wSub->weapon_type_id;
            } else {
                if (! empty($data['weapon_type'])) {
                    $wType        = WeaponType::firstOrCreate(['name' => trim($data['weapon_type'])]);
                    $weaponTypeId = $wType->id;
                }
                $newWSub         = WeaponSubType::create(['name' => trim($data['weapon_subtype']), 'weapon_type_id' => $weaponTypeId]);
                $weaponSubtypeId = $newWSub->id;
            }
        } elseif (! empty($data['weapon_type'])) {
            $wType        = WeaponType::firstOrCreate(['name' => trim($data['weapon_type'])]);
            $weaponTypeId = $wType->id;
        }

        $motiveId = 0;
        if (! empty($data['motive'])) {
            $motiveId = Motive::firstOrCreate(['name' => $data['motive']])->id;
        }

        $motiveSpecificId = null;
        if (! empty($data['motive_specific'])) {
            $motiveSpecificId = MotivesSpecific::firstOrCreate(['name' => $data['motive_specific']])->id;
        }

        $attackGroupId = $attackSubgroupId = 0;
        if (! empty($data['attack_group'])) {
            $ag = AttackGroup::where('name', $data['attack_group'])->first();
            if ($ag) {
                $attackGroupId    = $ag->id;
                $attackSubgroupId = $ag->attack_subgroup_id;
            } else {
                $subgroupId = 0;
                if (! empty($data['attack_group_sub'])) {
                    $asg        = AttackSubGroup::firstOrCreate(['name' => $data['attack_group_sub']]);
                    $subgroupId = $asg->id;
                } else {
                    $unknownSub = AttackSubGroup::where('name', 'Unknown Group')->first();
                    $subgroupId = $unknownSub ? $unknownSub->id : 0;
                }
                $newAg            = AttackGroup::create(['name' => $data['attack_group'], 'attack_subgroup_id' => $subgroupId]);
                $attackGroupId    = $newAg->id;
                $attackSubgroupId = $subgroupId;
            }
        } elseif (! empty($data['attack_group_sub'])) {
            $attackSubgroupId = AttackSubGroup::firstOrCreate(['name' => $data['attack_group_sub']])->id;
        }

        // ── 9. COORDINATES ────────────────────────────────────────────────────
        $latitude  = ! empty($data['latitude'])  ? $data['latitude']  : ($lgaRecord->latitude  ?? '');
        $longitude = ! empty($data['longitude']) ? $data['longitude'] : ($lgaRecord->longitude ?? '');

        // ── 10. IMAGE ─────────────────────────────────────────────────────────
        $imageName = '';
        if (! empty($data['image'])) {
            $imageContent = @file_get_contents($data['image']);
            if ($imageContent !== false) {
                $imageName = 'images/incidents/' . Carbon::now()->format('YmdHisu') . '.png';
                Storage::put($imageName, $imageContent);
            }
        }

        // ── 11. UNIQUE EVENT ID ───────────────────────────────────────────────
        usleep(1000);
        $uniqueCode = Carbon::now()->format('YmdHisu');
        $email      = auth()->user()->email ?? 'import';

        // ── 12. TBLDATAENTRY payload ──────────────────────────────────────────
        $tableDataEntry = [
            'import_id'               => $importId,   // ← linked to history
            'eventid'                 => $uniqueCode,
            'location'                => $latLong->State,
            'lga'                     => $neighbourhood->lga_name ?? $latLong->LGA ?? '',
            'neighbourhood'           => $neighbourhood->id ?? '',
            'lga_lat'                 => $latitude,
            'lga_long'                => $longitude,
            'eventdate'               => $date->format('m/d/Y'),
            'eventdateToUse'          => $date->format('Y-m-d'),
            'dd'                      => $date->format('d'),
            'eventday'                => $date->format('d'),
            'week'                    => $date->format('W'),
            'mm'                      => $date->format('m'),
            'eventmonth'              => $date->format('m'),
            'YY'                      => $date->format('Y'),
            'eventyear'               => $date->format('Y'),
            'riskfactors'             => $riskfactorName,
            'riskindicators'          => $riskindicatorValue,
            'subcategory'             => $data['subcategory']  ?? null,
            'impact'                  => $impactValue,
            'author'                  => $email,
            'datecreated'             => Carbon::now()->format('M d, Y'),
            'auditauthor'             => $email,
            'auditdatecreated'        => Carbon::now()->format('M d, Y'),
            'audittimecreated'        => Carbon::now()->format('h:i:s a'),
            'month_pro'               => Carbon::now()->format('M'),
            'month_pro2'              => Carbon::now()->format('M'),
            'eventtime'               => 0,
            'extended'                => $data['extended']     ?? null,
            'week_day'                => $data['week_day']     ?? null,
            'Casualties_count'        => ($data['deaths_count'] ?? '') !== '' ? (int) $data['deaths_count'] : null,
            'Injuries_count'          => ! empty($data['injuries_count']) ? $data['injuries_count'] : null,
            'latitude'                => $latitude,
            'longitude'               => $longitude,
            'city'                    => '',
            'accused'                 => $data['accused']      ?? '',
            'victim'                  => $data['victim']       ?? null,
            'caption'                 => $data['caption'],
            'day_period'              => $dayPeriodId,
            'target_type'             => $targetTypeId,
            'target_subtype'          => $targetSubtypeId,
            'weapon_type'             => $weaponTypeId,
            'weapon_subtype'          => $weaponSubtypeId,
            'motive'                  => $motiveId,
            'motive_specific'         => $motiveSpecificId,
            'target_specific'         => $data['target_specific'] ?? null,
            'add_notes'               => $data['add_notes']       ?? null,
            'attack_group_name'       => $attackGroupId,
            'attack_group_sub_name'   => $attackSubgroupId,
            'ransom'                  => $data['ransom']  ?? null,
            'amount'                  => $data['amount']  ?? null,
            'risk_indicator_specific' => $data['done']    ?? null,
            'affected_industry'       => $data['affected_industry']  ?? null,
            'business_report'         => $data['business_report']    ?? null,
            'business_advisory'       => $data['business_advisory']  ?? null,
            'associated_risks'        => $data['associated_risks']   ?? null,
        ];

        // ── 13. TBLWEEKLYDATAENTRY payload ───────────────────────────────────
        //   THE FIX: 'import_id' is now explicitly included here.
        //   Previously this field was written into $tableDataEntry but was
        //   simply omitted from $weeklyDataEntry, so the column stayed NULL
        //   even though the migration and the weekly model both support it.
        $weeklyDataEntry = [
            'import_id'        => $importId,   // ← THE FIX — was missing here
            'eventid'          => $uniqueCode,
            'dday'             => $date->format('d'),
            'dmonth'           => $date->format('M'),
            'monthcorrected'   => $date->format('M'),
            'smallmonth'       => $date->format('M'),
            'datecorrected'    => $date->format('M d, Y'),
            'dyear'            => $date->format('Y'),
            'dweek'            => $date->format('W'),
            'reportdate'       => Carbon::now()->format('M d, Y'),
            'riskfactor'       => $riskfactorName,
            'riskindicator'    => $riskindicatorValue,
            'subcategory'      => $data['subcategory']    ?? '',
            'caption'          => $data['caption'],
            'content'          => $data['weekly_summary'] ?? '',
            'link1'            => $data['source_link']    ?? '',
            'author1'          => $email,
            'director'         => $email,
            'director2'        => $email,
            'lga'              => $neighbourhood->lga_name ?? $latLong->LGA ?? '',
            'neighbourhood'    => $neighbourhood->id ?? '',
            'lga_lat'          => $latitude,
            'lga_long'         => $longitude,
            'status'           => 'unread',
            'location'         => $latLong->State,
            'auditauthor'      => $email,
            'auditdatecreated' => Carbon::now()->format('M d, Y'),
            'audittimecreated' => Carbon::now()->format('h:i:s a'),
            'checked'          => 'Yes',
            'hashtag'          => $data['hashtag']        ?? null,
            'news'             => 'No',
            'Casualties_count' => ($data['deaths_count'] ?? '') !== '' ? (int) $data['deaths_count'] : null,
            'Injuries_count'   => ! empty($data['injuries_count']) ? $data['injuries_count'] : null,
            'latitude'         => $latitude,
            'longitude'        => $longitude,
            'city'             => '',
            'accused'          => $data['accused']        ?? '',
            'victim'           => ! empty($data['victim']) ? $data['victim'] : null,
            'image'            => $imageName ? 'storage/app/' . $imageName : '',
            'day_period'       => $dayPeriodId,
            'target_type'      => $targetTypeId,
            'target_subtype'   => $targetSubtypeId,
            'weapon_type'      => $weaponTypeId,
            'weapon_subtype'   => $weaponSubtypeId,
            'motive'           => $motiveId,
            'motive_specific'  => $motiveSpecificId,
            'target_specific'  => $data['target_specific'] ?? null,
            'add_notes'        => $data['add_notes']       ?? null,
            'attack_group_name' => $attackGroupId,
            'ransom'           => $data['ransom']  ?? null,
            'amount'           => $data['amount']  ?? null,
            'impact_level'     => $data['impact_level']     ?? null,
            'impact_rationale' => $data['impact_rationale'] ?? null,
            'link3'            => $data['similar_news_link'] ?? null,
            'associated_risks' => $data['associated_risks'] ?? null,
        ];

        // ── 14. INSERT BOTH ROWS atomically ───────────────────────────────────
        DB::transaction(function () use ($tableDataEntry, $weeklyDataEntry) {
            DB::table('tbldataentry')->insert($tableDataEntry);
            DB::table('tblweeklydataentry')->insert($weeklyDataEntry);
        });

        Log::info('[DataImport] Row inserted', [
            'row'       => $data['row_num'],
            'eventid'   => $uniqueCode,
            'import_id' => $importId,
        ]);

        return ['success' => true, 'year' => $date->format('Y')];
    }

    // =========================================================================
    // PRIVATE — cache bust after successful import
    // =========================================================================

    private function bustRiskCaches(array $years): void
    {
        foreach (array_unique($years) as $year) {
            Cache::forget("composite_index:{$year}");
        }
        foreach (['header_states_list', 'tblriskfactors_weighted', 'indicator_factor_weight_map', 'correction_factors_all'] as $key) {
            Cache::forget($key);
        }
        Log::info('[DataImport] Risk caches busted', ['years' => $years]);
    }
}
