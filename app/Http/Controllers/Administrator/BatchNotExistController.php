<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Services\AreaService;
use App\Services\BatchNotExistService;
use App\Services\PeriodService;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BatchNotExistController extends Controller
{
    /**
     * @var BatchNotExistService
     */
    private BatchNotExistService $batchNotExistService;

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
     * @param BatchNotExistService $batchNotExistService
     * @param AreaService $areaService
     * @param PeriodService $periodService
     */
    public function __construct(BatchNotExistService $batchNotExistService, AreaService $areaService, PeriodService $periodService)
    {
        $this->batchNotExistService = $batchNotExistService;
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
        return inertia('Administrator/BatchNotExists/Index', [
            'area' => $area,
            'period' => $period,
            'areas' => $this->areaService->collection()->toArray(),
            'periods' => $this->periodService->collection()->toArray(),
            'stocks' => $this->batchNotExistService->tableData($area, $period)
        ])->table(function (InertiaTable $table) {
            $this->batchNotExistService->tableMeta($table);
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
        return $this->batchNotExistService->export($request);
    }
}
