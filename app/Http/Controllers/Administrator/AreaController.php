<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Services\AreaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AreaController extends Controller
{
    /**
     * @var AreaService
     */
    private AreaService $areaService;

    /**
     * Create a new Controller instance.
     *
     * @param AreaService $areaService
     */
    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response|ResponseFactory
     */
    public function index(): Response|ResponseFactory
    {
        return inertia('Administrator/Areas/Index', [
            'areas' => $this->areaService->tableData(),
            'template' => $this->areaService->template()
        ])->table(function (InertiaTable $table) {
            $this->areaService->tableMeta($table);
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
        $this->areaService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Area $area
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Area $area): RedirectResponse
    {
        $this->areaService->update($request, $area);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Area $area
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(Area $area): RedirectResponse
    {
        $this->areaService->destroy($area);
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
        $this->areaService->import($request);
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
        return $this->areaService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        return response()->json($this->areaService->single($request)?->toJson());
    }

    /**
     * Generate the JSON-formatted resource collection data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonCollection(Request $request): JsonResponse
    {
        $data = $this->areaService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count()
        ]);
    }
}
