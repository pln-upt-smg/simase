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
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
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
     * @return Response|ResponseFactory
     */
    public function index(Request $request): Response|ResponseFactory
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
     * @return Response|ResponseFactory
     */
    public function create(): Response|ResponseFactory
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
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(Request $request): RedirectResponse
    {
        $this->actualStockService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param ActualStock $stock
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, ActualStock $stock): RedirectResponse
    {
        $this->actualStockService->update($request, $stock);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ActualStock $stock
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(ActualStock $stock): RedirectResponse
    {
        $this->actualStockService->destroy($stock);
        return back();
    }
}
