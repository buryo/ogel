<?php

namespace App\Services;

use App\{
    Http\Controllers\Traits\ReturnsDates,
    Http\Controllers\BaseController,
    Production,
    Runtime,
};
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MachineService extends BaseController
{
    /**
     * @var array
     */
    public $gross_productions;

    /**
     * @var array
     */
    public $net_productions;

    /**
     * @var array
     */
    public $scrap_percentages;

    /**
     * @var array
     */
    public $downtime_percentages;

    public function __construct()
    {
        $exampleDay = "2018-01-07";

        $this->getGrossProduction($exampleDay);
        $this->getNetProduction($exampleDay);
        $this->getScrapPercentage($exampleDay);
        $this->getDownTimePercentage($exampleDay);
    }

    /**
     * Get net production of all machines (gross - scrap).
     *
     * @param  string  $day
     * @return void
     */
    public function getGrossProduction($day): void
    {
        $date = ReturnsDates::dateHelper($day);

        $gross_productions = Production::whereBetween('datetime_from', [$date['from'], $date['to']])
            ->whereIn('variable_name', ['PRODUCTION', 'SCRAP'])
            ->groupBy('machine_name')
            ->selectRaw('machine_name, sum(value) as grossProduction')
            ->orderBy('machine_name', 'asc')
            ->get();

        $this->gross_productions = $gross_productions;
    }

    /**
     * Get net production of all machines (gross - scrap).
     *
     * @param  string  $day
     * @return void
     */
    public function getNetProduction($day): void
    {
        $date = ReturnsDates::dateHelper($day);

        $net_production = Production::whereBetween('datetime_from', [$date['from'], $date['to']])
            ->where('variable_name', '=', 'PRODUCTION')
            ->groupBy('machine_name', 'variable_name')
            ->selectRaw('machine_name as machine, sum(value) as production')
            ->orderBy('machine_name', 'asc')
            ->get();

        $this->net_productions = $net_production;
    }

    /**
     * Get scrap production of all machines, specific day in percentage.
     *
     * @param  string  $day
     * @return void
     */
    public function getScrapPercentage($day): void
    {
        $date = ReturnsDates::dateHelper($day);

        $machines_scrap = Production::whereBetween('datetime_from', [$date['from'], $date['to']])
            ->where('variable_name', '=', 'SCRAP')
            ->groupBy('machine_name', 'variable_name')
            ->selectRaw('machine_name, sum(value) as scrap')
            ->orderBy('machine_name', 'asc')
            ->get();

        foreach ($machines_scrap as $machine) {
            foreach ($this->gross_productions as $gross_production) {
                $scrap_percentages[$machine->machine_name] = $machine->scrap * 100 / $gross_production->grossProduction;
            }
        }

        $this->scrap_percentages = $scrap_percentages;
    }

    /**
     * Get down time of all machines, specific day in percentage.
     *
     * @param  string  $day
     * @return void
     */
    public function getDownTimePercentage($day = "2018-01-07"): void
    {
        $date = ReturnsDates::dateHelper($day);

        // Counting total rows and sum running.
        $runtimes = Runtime::whereBetween('datetime', [$date['from'], $date['to']])
            ->selectRaw('machine_name, count(*) as total, sum(isrunning) as running')
            ->groupBy('machine_name')
            ->orderBy('machine_name', 'asc')
            ->get();

        // Calculating the percentage.
        foreach ($runtimes as $runtime) {
            $downtime_percentages[$runtime->machine_name] = 100 * ($runtime->running / $runtime->total);
        }

        $this->downtime_percentages = $downtime_percentages;
    }
}
