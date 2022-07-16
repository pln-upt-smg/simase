<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\AssetType;
use App\Services\AssetTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AssetTypeController extends Controller
{
    /**
     * @var AssetTypeService
     */
    private AssetTypeService $assetTypeService;

    /**
     * Create a new Controller instance.
     *
     * @param AssetTypeService $assetTypeService
     */
    public function __construct(AssetTypeService $assetTypeService)
    {
        $this->assetTypeService = $assetTypeService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/AssetTypes/Index', [
            'asset_types' => $this->assetTypeService->tableData(),
            'template' => $this->assetTypeService->template(),
        ])->table(function (InertiaTable $table) {
            $this->assetTypeService->tableMeta($table);
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
        $this->assetTypeService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AssetType $areaType
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(
        Request $request,
        AssetType $areaType
    ): RedirectResponse {
        $this->assetTypeService->update($request, $areaType);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AssetType $areaType
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(AssetType $areaType): RedirectResponse
    {
        $this->assetTypeService->destroy($areaType);
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
        $this->assetTypeService->import($request);
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
        return $this->assetTypeService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->assetTypeService->single($request);
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
        $data = $this->assetTypeService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
