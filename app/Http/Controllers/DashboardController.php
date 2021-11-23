<?php

namespace App\Http\Controllers;

use App\Services\AreaService;
use App\Services\FinalSummaryService;
use App\Services\PeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class DashboardController extends Controller
{
    /**
     * @var AreaService
     */
    private AreaService $areaService;

    /**
     * @var PeriodService
     */
    private PeriodService $periodService;

    /**
     * @var FinalSummaryService
     */
    private FinalSummaryService $finalSummaryService;

    /**
     * Create a new Controller instance.
     *
     * @param AreaService $areaService
     * @param PeriodService $periodService
     * @param FinalSummaryService $finalSummaryService
     */
    public function __construct(AreaService $areaService, PeriodService $periodService, FinalSummaryService $finalSummaryService)
    {
        $this->areaService = $areaService;
        $this->periodService = $periodService;
        $this->finalSummaryService = $finalSummaryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response|ResponseFactory|RedirectResponse
     */
    public function index(Request $request): Response|ResponseFactory|RedirectResponse
    {
        if (is_null(auth()->user())) {
            return redirect()->route('login');
        }
        if (auth()->user()?->load('role')->role->isOperator()) {
            return redirect()->route('stocks.create');
        }
        $area = $this->areaService->resolve($request);
        $period = $this->periodService->resolve($request);
        $areas = $this->areaService->collection();
        return inertia('Administrator/Dashboard/Index', [
            'area' => $area,
            'period' => $period,
            'areaIds' => $areas->pluck('id')->toArray(),
            'areas' => $areas->pluck('name')->toArray(),
            'periods' => $this->periodService->collection()->toArray(),
            'areaFinalSummaries' => $this->finalSummaryService->chart($period),
            'gapValueRank' => $this->finalSummaryService->gapValueRankTableData($area, $period)
        ])->table(function (InertiaTable $table) {
            $this->finalSummaryService->tableMeta($table);
        });
    }
}
