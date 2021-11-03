<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\ProductMaterial;
use App\Services\AreaService;
use App\Services\PeriodService;
use App\Services\ProductMaterialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProductMaterialController extends Controller
{
    /**
     * @var ProductMaterialService
     */
    private ProductMaterialService $productMaterialService;

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
     * @param ProductMaterialService $productMaterialService
     * @param AreaService $areaService
     * @param PeriodService $periodService
     */
    public function __construct(ProductMaterialService $productMaterialService, AreaService $areaService, PeriodService $periodService)
    {
        $this->productMaterialService = $productMaterialService;
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
        return inertia('Administrator/Products/Materials/Index', [
            'area' => $area,
            'period' => $period,
            'areas' => $this->areaService->collection()->toArray(),
            'periods' => $this->periodService->collection()->toArray(),
            'productMaterials' => $this->productMaterialService->tableData($area, $period),
            'template' => $this->productMaterialService->template()
        ])->table(function (InertiaTable $table) {
            $this->productMaterialService->tableMeta($table);
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
        $this->productMaterialService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param ProductMaterial $material
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, ProductMaterial $material): RedirectResponse
    {
        $this->productMaterialService->update($request, $material);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ProductMaterial $material
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(ProductMaterial $material): RedirectResponse
    {
        $this->productMaterialService->destroy($material);
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
        $this->productMaterialService->import($request);
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
        return $this->productMaterialService->export($request);
    }
}
