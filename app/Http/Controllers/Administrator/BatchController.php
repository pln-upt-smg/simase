<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Services\BatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BatchController extends Controller
{
    /**
     * @var BatchService
     */
    private BatchService $batchService;

    /**
     * Create a new Controller instance.
     *
     * @param BatchService $batchService
     */
    public function __construct(BatchService $batchService)
    {
        $this->batchService = $batchService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response|ResponseFactory
     */
    public function index(): Response|ResponseFactory
    {
        return inertia('Administrator/Batches/Index', [
            'batches' => $this->batchService->tableData(),
            'template' => $this->batchService->template()
        ])->table(function (InertiaTable $table) {
            $this->batchService->tableMeta($table);
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
        $this->batchService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Batch $batch
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Batch $batch): RedirectResponse
    {
        $this->batchService->update($request, $batch);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Batch $batch
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(Batch $batch): RedirectResponse
    {
        $this->batchService->destroy($batch);
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
        $this->batchService->import($request);
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
        return $this->batchService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        return response()->json($this->batchService->single($request)?->toJson());
    }

    /**
     * Generate the JSON-formatted resource collection data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonCollection(Request $request): JsonResponse
    {
        $data = $this->batchService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count()
        ]);
    }
}
