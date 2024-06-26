<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Services\{AssetService, AssetTypeService};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AssetController extends Controller
{
    /**
     * @var AssetService
     */
    private AssetService $assetService;

    /**
     * @var AssetTypeService
     */
    private AssetTypeService $assetTypeService;

    /**
     * Create a new Controller instance.
     *
     * @param AssetService $assetService
     * @param AssetTypeService $assetTypeService
     */
    public function __construct(
        AssetService $assetService,
        AssetTypeService $assetTypeService
    ) {
        $this->assetService = $assetService;
        $this->assetTypeService = $assetTypeService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/Assets/Index', [
            'asset_types' => $this->assetTypeService->collection()->toArray(),
            'assets' => $this->assetService->tableData(),
            'template' => $this->assetService->template(),
        ])->table(function (InertiaTable $table) {
            $this->assetService->tableMeta($table);
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
        $this->assetService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Asset $asset
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $this->assetService->update($request, $asset);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Asset $asset
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(Asset $asset): RedirectResponse
    {
        $this->assetService->destroy($asset);
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
        $this->assetService->import($request);
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
        return $this->assetService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->assetService->single($request);
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
        $data = $this->assetService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
