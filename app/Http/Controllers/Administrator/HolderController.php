<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Holder;
use App\Services\HolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class HolderController extends Controller
{
    /**
     * @var HolderService
     */
    private HolderService $holderService;

    /**
     * Create a new Controller instance.
     *
     * @param HolderService $holderService
     */
    public function __construct(HolderService $holderService)
    {
        $this->holderService = $holderService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/Holders/Index', [
            'holders' => $this->holderService->tableData(),
            'template' => $this->holderService->template(),
        ])->table(function (InertiaTable $table) {
            $this->holderService->tableMeta($table);
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
        $this->holderService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Holder $holder
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Holder $holder): RedirectResponse
    {
        $this->holderService->update($request, $holder);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Holder $holder
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(Holder $holder): RedirectResponse
    {
        $this->holderService->destroy($holder);
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
        $this->holderService->import($request);
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
        return $this->holderService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->holderService->single($request);
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
        $data = $this->holderService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
