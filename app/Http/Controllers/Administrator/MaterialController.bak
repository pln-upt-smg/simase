<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Services\MaterialService;
use App\Services\PeriodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
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
     * @var PeriodService
     */
    private PeriodService $periodService;

    /**
     * Create a new Controller instance.
     *
     * @param MaterialService $materialService
     * @param PeriodService $periodService
     */
    public function __construct(MaterialService $materialService, PeriodService $periodService)
    {
        $this->materialService = $materialService;
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
        return inertia('Administrator/Materials/Index', [
            'period' => $period,
            'periods' => $this->periodService->collection()->toArray(),
            'materials' => $this->materialService->tableData($period),
            'template' => $this->materialService->template()
        ])->table(function (InertiaTable $table) {
            $this->materialService->tableMeta($table);
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
        $this->materialService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Material $material
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Material $material): RedirectResponse
    {
        $this->materialService->update($request, $material);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Material $material
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(Material $material): RedirectResponse
    {
        $this->materialService->destroy($material);
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
        $this->materialService->import($request);
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
        return $this->materialService->export($request);
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        return response()->json($this->materialService->single($request)?->toJson());
    }

    /**
     * Generate the JSON-formatted resource collection data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonCollection(Request $request): JsonResponse
    {
        $data = $this->materialService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count()
        ]);
    }
}
