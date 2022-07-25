<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\AssetSubmission;
use App\Services\{AssetSubmissionService, PriorityService};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AssetSubmissionController extends Controller
{
    /**
     * @var AssetSubmissionService
     */
    private AssetSubmissionService $assetSubmissionService;

    /**
     * @var PriorityService
     */
    private PriorityService $priorityService;

    /**
     * Create a new Controller instance.
     *
     * @param AssetSubmissionService $assetSubmissionService
     * @param PriorityService $priorityService
     */
    public function __construct(
        AssetSubmissionService $assetSubmissionService,
        PriorityService $priorityService
    ) {
        $this->assetSubmissionService = $assetSubmissionService;
        $this->priorityService = $priorityService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/Assets/Submissions/Index', [
            'priorities' => $this->priorityService->collection()->toArray(),
            'asset_submissions' => $this->assetSubmissionService->tableData(),
        ])->table(function (InertiaTable $table) {
            $this->assetSubmissionService->tableMeta($table);
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
        $this->assetSubmissionService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AssetSubmission $submission
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(
        Request $request,
        AssetSubmission $submission
    ): RedirectResponse {
        $this->assetSubmissionService->update($request, $submission);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AssetSubmission $submission
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(AssetSubmission $submission): RedirectResponse
    {
        $this->assetSubmissionService->destroy($submission);
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
        $this->assetSubmissionService->import($request);
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
        return $this->assetSubmissionService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->assetSubmissionService->single($request);
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
        $data = $this->assetSubmissionService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
