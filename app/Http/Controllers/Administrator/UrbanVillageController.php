<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\UrbanVillage;
use App\Services\UrbanVillageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class UrbanVillageController extends Controller
{
    /**
     * @var UrbanVillageService
     */
    private UrbanVillageService $urbanVillageService;

    /**
     * Create a new Controller instance.
     *
     * @param UrbanVillageService $urbanVillageService
     */
    public function __construct(UrbanVillageService $urbanVillageService)
    {
        $this->urbanVillageService = $urbanVillageService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/UrbanVillages/Index', [
            'urban_villages' => $this->urbanVillageService->tableData(),
            'template' => $this->urbanVillageService->template(),
        ])->table(function (InertiaTable $table) {
            $this->urbanVillageService->tableMeta($table);
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
        $this->urbanVillageService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param UrbanVillage $village
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(
        Request $request,
        UrbanVillage $village
    ): RedirectResponse {
        $this->urbanVillageService->update($request, $village);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UrbanVillage $village
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(UrbanVillage $village): RedirectResponse
    {
        $this->urbanVillageService->destroy($village);
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
        $this->urbanVillageService->import($request);
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
        return $this->urbanVillageService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->urbanVillageService->single($request);
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
        $data = $this->urbanVillageService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
