<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OperatorService;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
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
     * @var RoleService
     */
    private RoleService $roleService;

    /**
     * Create a new Controller instance.
     *
     * @param OperatorService $operatorService
     * @param RoleService $roleService
     */
    public function __construct(OperatorService $operatorService, RoleService $roleService)
    {
        $this->operatorService = $operatorService;
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response|ResponseFactory
     */
    public function index(): Response|ResponseFactory
    {
        return inertia('Administrator/Operators/Index', [
            'roles' => $this->roleService->collection()->toArray(),
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
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(Request $request): RedirectResponse
    {
        $this->operatorService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $operator
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, User $operator): RedirectResponse
    {
        $this->operatorService->update($request, $operator);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $operator
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(User $operator): RedirectResponse
    {
        $this->operatorService->destroy($operator);
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
