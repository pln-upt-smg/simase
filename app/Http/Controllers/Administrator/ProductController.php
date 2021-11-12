<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
     * @var PeriodService
     */
    private PeriodService $periodService;

    /**
     * Create a new Controller instance.
     *
     * @param ProductService $productService
     * @param PeriodService $periodService
     */
    public function __construct(ProductService $productService, PeriodService $periodService)
    {
        $this->productService = $productService;
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
        return inertia('Administrator/Products/Index', [
            'period' => $period,
            'periods' => $this->periodService->collection()->toArray(),
            'products' => $this->productService->tableData($period),
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
    public function json(Request $request): JsonResponse
    {
        return response()->json($this->productService->single($request)?->toJson());
    }

    /**
     * Generate the JSON-formatted resource collection data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonCollection(Request $request): JsonResponse
    {
        $data = $this->productService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count()
        ]);
    }
}
