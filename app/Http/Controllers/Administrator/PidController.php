<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Services\AreaService;
use App\Services\PeriodService;
use App\Services\PidService;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PidController extends Controller
{
    /**
     * @var PidService
     */
    private PidService $pidService;

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
     * @param PidService $pidService
     * @param AreaService $areaService
     * @param PeriodService $periodService
     */
    public function __construct(PidService $pidService, AreaService $areaService, PeriodService $periodService)
    {
        $this->pidService = $pidService;
        $this->areaService = $areaService;
        $this->periodService = $periodService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response|ResponseFactory
     */
    public function index(Request $request): Response|ResponseFactory
    {
        $area = $this->areaService->resolve($request);
        $period = $this->periodService->resolve($request);
        return inertia('Administrator/Pids/Index', [
            'area' => $area,
            'period' => $period,
            'areas' => $this->areaService->collection()->toArray(),
            'periods' => $this->periodService->collection()->toArray(),
            'stocks' => $this->pidService->tableData($area, $period)
        ])->table(function (InertiaTable $table) {
            $this->pidService->tableMeta($table);
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
        return $this->pidService->export($request);
    }
}
