<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OperatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class OperatorController extends Controller
{
    /**
     * @var OperatorService
     */
    private OperatorService $operatorService;

    /**
     * Create a new Controller instance.
     *
     * @param OperatorService $operatorService
     */
    public function __construct(OperatorService $operatorService)
    {
        $this->operatorService = $operatorService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|ResponseFactory
     */
    public function index(): \Inertia\Response|ResponseFactory
    {
        return inertia('Administrator/Operators/Index', [
            'operators' => $this->operatorService->tableData(),
            'template' => $this->operatorService->template()
        ])->table(function (InertiaTable $table) {
            $this->operatorService->tableMeta($table);
        });
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function store(Request $request): Response|RedirectResponse
    {
        $this->operatorService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $operator
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, User $operator): Response|RedirectResponse
    {
        $this->operatorService->update($request, $operator);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $operator
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function destroy(User $operator): Response|RedirectResponse
    {
        $this->operatorService->destroy($operator);
        return back();
    }

    /**
     * Import the resource from file.
     *
     * @param Request $request
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function import(Request $request): Response|RedirectResponse
    {
        $this->operatorService->import($request);
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
        return $this->operatorService->export();
    }
}
