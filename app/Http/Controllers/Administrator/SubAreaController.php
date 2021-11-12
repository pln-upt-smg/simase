<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\SubArea;
use App\Services\SubAreaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SubAreaController extends Controller
{
    /**
     * @var SubAreaService
     */
    private SubAreaService $subAreaService;

    /**
     * Create a new Controller instance.
     *
     * @param SubAreaService $subAreaService
     */
    public function __construct(SubAreaService $subAreaService)
    {
        $this->subAreaService = $subAreaService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response|ResponseFactory
     */
    public function index(): Response|ResponseFactory
    {
        return inertia('Administrator/SubAreas/Index', [
            'subAreas' => $this->subAreaService->tableData(),
            'template' => $this->subAreaService->template()
        ])->table(function (InertiaTable $table) {
            $this->subAreaService->tableMeta($table);
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
        $this->subAreaService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param SubArea $subArea
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, SubArea $subArea): RedirectResponse
    {
        $this->subAreaService->update($request, $subArea);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SubArea $subArea
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(SubArea $subArea): RedirectResponse
    {
        $this->subAreaService->destroy($subArea);
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
        $this->subAreaService->import($request);
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
        return $this->subAreaService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        return response()->json($this->subAreaService->single($request)?->toJson());
    }

    /**
     * Generate the JSON-formatted resource collection data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonCollection(Request $request): JsonResponse
    {
        $data = $this->subAreaService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count()
        ]);
    }
}
