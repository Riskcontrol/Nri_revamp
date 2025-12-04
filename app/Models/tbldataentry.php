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


    public function tblweeklydataentry()
    {
        return $this->belongsTo(tblweeklydataentry::class, 'id');
    }
}
