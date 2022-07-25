<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\AssetTransfer;
use App\Services\{AssetTransferService, PriorityService};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AssetTransferController extends Controller
{
    /**
     * @var AssetTransferService
     */
    private AssetTransferService $assetTransferService;

    /**
     * @var PriorityService
     */
    private PriorityService $priorityService;

    /**
     * Create a new Controller instance.
     *
     * @param AssetTransferService $assetTransferService
     * @param PriorityService $priorityService
     */
    public function __construct(
        AssetTransferService $assetTransferService,
        PriorityService $priorityService
    ) {
        $this->assetTransferService = $assetTransferService;
        $this->priorityService = $priorityService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/Assets/Transfers/Index', [
            'priorities' => $this->priorityService->collection()->toArray(),
            'asset_transfers' => $this->assetTransferService->tableData(),
        ])->table(function (InertiaTable $table) {
            $this->assetTransferService->tableMeta($table);
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
        $this->assetTransferService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AssetTransfer $transfer
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(
        Request $request,
        AssetTransfer $transfer
    ): RedirectResponse {
        $this->assetTransferService->update($request, $transfer);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AssetTransfer $transfer
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(AssetTransfer $transfer): RedirectResponse
    {
        $this->assetTransferService->destroy($transfer);
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
        $this->assetTransferService->import($request);
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
        return $this->assetTransferService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->assetTransferService->single($request);
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
        $data = $this->assetTransferService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
