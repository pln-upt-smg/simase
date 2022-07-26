<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\SubDistrict;
use App\Services\SubDistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SubDistrictController extends Controller
{
    /**
     * @var SubDistrictService
     */
    private SubDistrictService $subDistrictService;

    /**
     * Create a new Controller instance.
     *
     * @param SubDistrictService $subDistrictService
     */
    public function __construct(SubDistrictService $subDistrictService)
    {
        $this->subDistrictService = $subDistrictService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/SubDistricts/Index', [
            'sub_districts' => $this->subDistrictService->tableData(),
            'template' => $this->subDistrictService->template(),
        ])->table(function (InertiaTable $table) {
            $this->subDistrictService->tableMeta($table);
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
        $this->subDistrictService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param SubDistrict $district
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(
        Request $request,
        SubDistrict $district
    ): RedirectResponse {
        $this->subDistrictService->update($request, $district);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SubDistrict $district
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(SubDistrict $district): RedirectResponse
    {
        $this->subDistrictService->destroy($district);
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
        $this->subDistrictService->import($request);
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
        return $this->subDistrictService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->subDistrictService->single($request);
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
        $data = $this->subDistrictService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
