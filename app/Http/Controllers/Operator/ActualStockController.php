<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\ActualStock;
use App\Services\ActualStockService;
use App\Services\AreaService;
use App\Services\PeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ActualStockController extends Controller
{
    /**
     * @var ActualStockService
     */
    private ActualStockService $actualStockService;

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
     * @param ActualStockService $actualStockService
     * @param AreaService $areaService
     * @param PeriodService $periodService
     */
    public function __construct(ActualStockService $actualStockService, AreaService $areaService, PeriodService $periodService)
    {
        $this->actualStockService = $actualStockService;
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
        $area = $this->areaService->resolve($request);
        $period = $this->periodService->resolve($request);
        return inertia('Operator/Stocks/Result/Index', [
            'area' => $area,
            'period' => $period,
            'areas' => $this->areaService->collection(),
            'periods' => $this->periodService->collection(),
            'stocks' => $this->actualStockService->tableData($area, $period, true),
            'template' => $this->actualStockService->template()
        ])->table(function (InertiaTable $table) {
            $this->actualStockService->tableMeta($table);
        });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return inertia('Operator/Stocks/Input/Index', [
            'areas' => $this->areaService->collection(),
            'periods' => $this->periodService->collection()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function store(Request $request): Response|RedirectResponse
    {
        $this->actualStockService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param ActualStock $actual
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, ActualStock $actual): RedirectResponse
    {
        $this->actualStockService->update($request, $actual);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ActualStock $actual
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(ActualStock $actual): RedirectResponse
    {
        $this->actualStockService->destroy($actual);
        return back();
    }

    /**
     * Import the resource from file.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function import(Request $request): RedirectResponse
    {
        $this->actualStockService->import($request);
        return back();
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
        return $this->actualStockService->export($request);
    }
}
