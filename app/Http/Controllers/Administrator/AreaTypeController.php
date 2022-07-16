<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\AreaType;
use App\Services\AreaTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AreaTypeController extends Controller
{
    /**
     * @var AreaTypeService
     */
    private AreaTypeService $areaTypeService;

    /**
     * Create a new Controller instance.
     *
     * @param AreaTypeService $areaTypeService
     */
    public function __construct(AreaTypeService $areaTypeService)
    {
        $this->areaTypeService = $areaTypeService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/AreaTypes/Index', [
            'area_types' => $this->areaTypeService->tableData(),
            'template' => $this->areaTypeService->template(),
        ])->table(function (InertiaTable $table) {
            $this->areaTypeService->tableMeta($table);
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
        $this->areaTypeService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AreaType $areaType
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(
        Request $request,
        AreaType $areaType
    ): RedirectResponse {
        $this->areaTypeService->update($request, $areaType);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AreaType $areaType
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(AreaType $areaType): RedirectResponse
    {
        $this->areaTypeService->destroy($areaType);
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
        $this->areaTypeService->import($request);
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
        return $this->areaTypeService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->areaTypeService->single($request);
        if (!is_null($data)) {
            $data = $data->toJson();
        }
        return response()->json($data);
    }

    /**
     * Generate the JSON-formatted resource collection data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonCollection(Request $request): JsonResponse
    {
        $data = $this->areaTypeService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
