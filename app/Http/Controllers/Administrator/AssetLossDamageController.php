<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\AssetLossDamage;
use App\Services\AssetLossDamageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AssetLossDamageController extends Controller
{
    /**
     * @var AssetLossDamageService
     */
    private AssetLossDamageService $assetLossDamageService;

    /**
     * Create a new Controller instance.
     *
     * @param AssetLossDamageService $assetLossDamageService
     */
    public function __construct(AssetLossDamageService $assetLossDamageService)
    {
        $this->assetLossDamageService = $assetLossDamageService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/Asset/LossDamages/Index', [
            'asset_loss_damages' => $this->assetLossDamageService->tableData(),
            'template' => $this->assetLossDamageService->template(),
        ])->table(function (InertiaTable $table) {
            $this->assetLossDamageService->tableMeta($table);
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
        $this->assetLossDamageService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AssetLossDamage $loss
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(
        Request $request,
        AssetLossDamage $loss
    ): RedirectResponse {
        $this->assetLossDamageService->update($request, $loss);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AssetLossDamage $loss
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(AssetLossDamage $loss): RedirectResponse
    {
        $this->assetLossDamageService->destroy($loss);
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
        $this->assetLossDamageService->import($request);
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
        return $this->assetLossDamageService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->assetLossDamageService->single($request);
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
        $data = $this->assetLossDamageService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
