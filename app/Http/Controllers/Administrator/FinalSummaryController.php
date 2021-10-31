<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Services\FinalSummaryService;
use App\Services\PeriodService;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class FinalSummaryController extends Controller
{
    /**
     * @var FinalSummaryService
     */
    private FinalSummaryService $finalSummaryService;

    /**
     * @var PeriodService
     */
    private PeriodService $periodService;

    /**
     * Create a new Controller instance.
     *
     * @param FinalSummaryService $finalSummaryService
     * @param PeriodService $periodService
     */
    public function __construct(FinalSummaryService $finalSummaryService, PeriodService $periodService)
    {
        $this->finalSummaryService = $finalSummaryService;
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
        $period = $this->periodService->resolve($request);
        return inertia('Administrator/Summaries/Index', [
            'period' => $period,
            'periods' => $this->periodService->collection()->toArray(),
            'summaries' => $this->finalSummaryService->tableData($period)
        ])->table(function (InertiaTable $table) {
            $this->finalSummaryService->tableMeta($table);
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
        return $this->finalSummaryService->export($request);
    }
}
