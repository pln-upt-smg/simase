<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\AreaService;
use App\Services\PeriodService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProductController extends Controller
{
    /**
     * @var ProductService
     */
    private ProductService $productService;

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
     * @param ProductService $productService
     * @param AreaService $areaService
     * @param PeriodService $periodService
     */
    public function __construct(ProductService $productService, AreaService $areaService, PeriodService $periodService)
    {
        $this->productService = $productService;
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
        return inertia('Administrator/Products/Index', [
            'area' => $area,
            'period' => $period,
            'areas' => $this->areaService->collection()->toArray(),
            'periods' => $this->periodService->collection()->toArray(),
            'products' => $this->productService->tableData($area, $period),
            'template' => $this->productService->template()
        ])->table(function (InertiaTable $table) {
            $this->productService->tableMeta($table);
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
        $this->productService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->productService->update($request, $product);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->destroy($product);
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
        $this->productService->import($request);
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
        return $this->productService->export($request);
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function productJson(Request $request): JsonResponse
    {
        $product = $this->productService->resolveProductCode($request);
        return response()->json($product?->toJson());
    }

    /**
     * Generate the JSON-formatted resource collection data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function productCodeJsonCollection(Request $request): JsonResponse
    {
        $codes = $this->productService->productCodeJsonCollection($request);
        return response()->json([
            'items' => $codes->toArray(),
            'total_count' => $codes->count()
        ]);
    }
}
