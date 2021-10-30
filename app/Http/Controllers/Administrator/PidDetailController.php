<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Services\AreaService;
use App\Services\PeriodService;
use App\Services\PidDetailService;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PidDetailController extends Controller
{
    /**
     * @var PidDetailService
     */
    private PidDetailService $pidDetailService;

    /**
     * @var AreaService
     */
    private AreaService $areaService;

    /**
     * @var PeriodService
     */
    private PeriodService $periodService;

    /**
     * Create a new Controller instance.
     *
     * @param PidDetailService $pidDetailService
     * @param AreaService $areaService
     * @param PeriodService $periodService
     */
    public function __construct(PidDetailService $pidDetailService, AreaService $areaService, PeriodService $periodService)
    {
        $this->pidDetailService = $pidDetailService;
        $this->areaService = $areaService;
        $this->periodService = $periodService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $period = $this->periodService->resolve($request);
        return inertia('Administrator/Pids/Details/Index', [
            'period' => $period,
            'areas' => $this->areaService->collection(),
            'periods' => $this->periodService->collection(),
            'stocks' => $this->pidDetailService->tableData($period),
            'areaStocks' => $this->pidDetailService->tableAreaData($period)
        ])->table(function (InertiaTable $table) {
            $this->pidDetailService->tableMeta($table);
        });
    }

    /**
     * Export the resource to specified file.
     *
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(Request $request): BinaryFileResponse
    {
        return $this->pidDetailService->export($request);
    }
}
