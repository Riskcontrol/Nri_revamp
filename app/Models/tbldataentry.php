<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;
use DatePeriod;
use DateInterval;

class tbldataentry extends Model
{
    use HasFactory;
    protected $table = 'tbldataentry';

    public $timestamps = false;
    protected $guarded = [];

    public static function getThreatInsightsGraphDataNukeCoder($dateMonth = "",  $dateyear = "", $searchVal = "", $riskfactor = "")
    {
        // dd($searchVal, $riskfactor, $dateyear, $dateMonth);

        // $graphData  = tbldataentry::query()
        //     ->select(DB::raw('trim(location) as location, trim(riskfactors) as riskfactors, count(id) as total'), 'eventdateToUse')
        //     ->where('eventmonth', $dateMonth)->where('eventyear', $dateyear)
        //     ->where('riskfactors', 'like', "%$riskfactor%")
        //     ->where('location', 'like', "%$searchVal%")
        //     ->groupBy('eventdateToUse')
        //     ->get();
        $graphData  = tbldataentry::query()
        ->select(DB::raw('eventdateToUse, count(id) as total'))
        ->where('eventmonth', $dateMonth)
        ->where('eventyear', $dateyear)
        ->where('riskfactors', 'like', "%$riskfactor%")
        ->where('location', 'like', "%$searchVal%")
        ->groupBy('eventdateToUse')
        ->orderBy('eventdateToUse')
        ->get();

        $allItems = [];

        foreach ($graphData as $key => $item) {
            $allItems[$key] = array($item->eventdateToUse, $item->total);
        }

        $dates = array_column($allItems, "0");

        //  $pp = date("Y-m-t", strtotime($allItems[0]["0"]));
        $dt = Carbon::createFromFormat('m/Y', $dateMonth . "/" . $dateyear);
        $start1 = $dt->startOfMonth()->format("Y-m-d");
        $end1 = $dt->endOfMonth()->format("Y-m-d");
        $start = new DateTime($start1);
        $end = new DateTime($end1);
        $range = new DatePeriod($start, new DateInterval('P1D'), $end);
        // dd(count($range));
        foreach ($range as $date) {
            // dd($date->format("Y-m-d"));
            //See if the current date exist is first array
            $find = array_search($date->format("Y-m-d"), $dates);
            // dd($find);
            if ($find !== false) {
                $result[] = $allItems[$find]; // if it does copy it to result array
            } else {
                // If not add it and create a total = 0
                $result[] = array($date->format("Y-m-d"), 0);
            }
        }
        // Since the loop does not loop all dates we need to add the last item to result.
        if (end($allItems) && $allItems[0] == $end1) {
            $result[] = end($allItems);
        } else {
            $result[] = array($end1, 0);
        }
        //   dd($result);
        return $result;
    }
    public static function getThreatInsightsGraphData($dateMonth = "",  $dateyear = "", $searchVal = "", $riskfactor = "")
    {
        $graphData = tbldataentry::query()
            ->select(DB::raw('eventmonth, eventyear, count(id) as total'))
            ->where('eventmonth', $dateMonth)
            ->where('eventyear', $dateyear)
            ->where('riskfactors', 'like', "%$riskfactor%")
            ->where('location', 'like', "%$searchVal%")
            ->groupBy('eventmonth', 'eventyear')
            ->orderBy('eventyear')
            ->orderBy('eventmonth')
            ->get();

        // Prepare the result array
        $result = [];
        $currentMonth = (int)$dateMonth;
        $currentYear = (int)$dateyear;

        // Initialize an array to hold data for each month of the year
        $monthsData = array_fill(1, 12, 0);

        // Fill the monthsData array with the data from the database
        foreach ($graphData as $item) {
            $monthsData[(int)$item->eventmonth] = $item->total;
        }

        // Iterate over each month to create the result array
        foreach ($monthsData as $month => $total) {
            // Format the month and year
            $formattedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
            $formattedYear = str_pad($currentYear, 4, '0', STR_PAD_LEFT);

            // Concatenate month and year to create the date string
            $date = "$formattedMonth $formattedYear";

            // Add data to the result array
            $result[] = [$date, $total];
        }

        return $result;
    }

    public function tblweeklydataentry()
    {
        return $this->belongsTo(tblweeklydataentry::class, 'id');
    }
}
