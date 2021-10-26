<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Quarter;
use App\Services\QuarterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Throwable;

class QuarterController extends Controller
{
    /**
     * @var QuarterService
     */
    private QuarterService $quarterService;

    /**
     * Create a new Controller instance.
     *
     * @param QuarterService $quarterService
     */
    public function __construct(QuarterService $quarterService)
    {
        $this->quarterService = $quarterService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|ResponseFactory
     */
    public function index(): \Inertia\Response|ResponseFactory
    {
        return inertia('Administrator/Quarters/Index', [
            'quarters' => $this->quarterService->tableData()
        ])->table(function (InertiaTable $table) {
            $this->quarterService->tableMeta($table);
        });
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
        $this->quarterService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Quarter $quarter
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Quarter $quarter): Response|RedirectResponse
    {
        $this->quarterService->update($request, $quarter);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Quarter $quarter
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function destroy(Quarter $quarter): Response|RedirectResponse
    {
        $this->quarterService->destroy($quarter);
        return back();
    }
}
