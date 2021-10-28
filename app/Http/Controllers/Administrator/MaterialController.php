<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Services\AreaService;
use App\Services\MaterialService;
use App\Services\PeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class MaterialController extends Controller
{
    /**
     * @var MaterialService
     */
    private MaterialService $materialService;

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
     * @param MaterialService $materialService
     * @param AreaService $areaService
     * @param PeriodService $periodService
     */
    public function __construct(MaterialService $materialService, AreaService $areaService, PeriodService $periodService)
    {
        $this->materialService = $materialService;
        $this->areaService = $areaService;
        $this->periodService = $periodService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Inertia\Response|ResponseFactory
     */
    public function index(Request $request): \Inertia\Response|ResponseFactory
    {
        $area = $this->areaService->resolve($request);
        $period = $this->periodService->resolve($request);
        return inertia('Administrator/Materials/Index', [
            'area' => $area,
            'period' => $period,
            'areas' => $this->areaService->collection(),
            'periods' => $this->periodService->collection(),
            'materials' => $this->materialService->tableData($area, $period),
            'template' => $this->materialService->template()
        ])->table(function (InertiaTable $table) {
            $this->materialService->tableMeta($table);
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
        $this->materialService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Material $material
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Material $material): Response|RedirectResponse
    {
        $this->materialService->update($request, $material);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Material $material
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function destroy(Material $material): Response|RedirectResponse
    {
        $this->materialService->destroy($material);
        return back();
    }

    /**
     * Import the resource from file.
     *
     * @param Request $request
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function import(Request $request): Response|RedirectResponse
    {
        $this->materialService->import($request);
        return back();
    }

    /**
     * Export the resource to specified file.
     *
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return $this->materialService->export();
    }
}
