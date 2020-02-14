<?php

namespace App\Http\Controllers;

use App\Http\Resources\MachineCollection;
use App\Services\MachineService;
use Illuminate\Http\Request;

class DashBoardController extends BaseController
{
    protected $machineService;

    public function __construct(MachineService $machineService)
    {
        $this->machineService = $machineService;
    }

    public function index()
    {
        $dashboardData = $this->machineService->net_productions;
        $scrap_percentages = $this->machineService->scrap_percentages;
        $downtime_percentages = $this->machineService->downtime_percentages;

        // Push scrap percentage and down time.
        foreach ($dashboardData as $key => $data) {
            $dashboardData[$key]["scrap_percentage"] = $scrap_percentages[$dashboardData[$key]["machine"]];
            $dashboardData[$key]["downtime_percentage"] = $downtime_percentages[$dashboardData[$key]["machine"]];
        }

        return $dashboardData;
    }
}
