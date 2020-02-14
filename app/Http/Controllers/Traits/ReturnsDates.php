<?php

namespace App\Http\Controllers\Traits;

use Carbon\Carbon;

trait ReturnsDates
{
    public static function dateHelper(string $day): array
    {
        $day = Carbon::parse($day);
        $date["from"] = $day->format('Y-m-d');
        $date["to"] = $day->addDay()->format('Y-m-d');

        return $date;
    }
}
