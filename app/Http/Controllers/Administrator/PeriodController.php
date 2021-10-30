<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Services\PeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Throwable;

class PeriodController extends Controller
{
    /**
     * @var PeriodService
     */
    private PeriodService $periodService;

    /**
     * Create a new Controller instance.
     *
     * @param PeriodService $periodService
     */
    public function __construct(PeriodService $periodService)
    {
        $this->periodService = $periodService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response|ResponseFactory
     */
    public function index(): Response|ResponseFactory
    {
        return inertia('Administrator/Periods/Index', [
            'periods' => $this->periodService->tableData()
        ])->table(function (InertiaTable $table) {
            $this->periodService->tableMeta($table);
        });
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
        $this->periodService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Period $period
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Period $period): RedirectResponse
    {
        $this->periodService->update($request, $period);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Period $period
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(Period $period): RedirectResponse
    {
        $this->periodService->destroy($period);
        return back();
    }
}
