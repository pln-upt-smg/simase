<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmployeeService;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class EmployeeController extends Controller
{
    /**
     * @var EmployeeService
     */
    private EmployeeService $employeeService;

    /**
     * @var RoleService
     */
    private RoleService $roleService;

    /**
     * Create a new Controller instance.
     *
     * @param EmployeeService $employeeService
     * @param RoleService $roleService
     */
    public function __construct(
        EmployeeService $employeeService,
        RoleService $roleService
    ) {
        $this->employeeService = $employeeService;
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/Employees/Index', [
            'roles' => $this->roleService->collection()->toArray(),
            'employees' => $this->employeeService->tableData(),
            'template' => $this->employeeService->template(),
        ])->table(function (InertiaTable $table) {
            $this->employeeService->tableMeta($table);
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
        $this->employeeService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $employee
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, User $employee): RedirectResponse
    {
        $this->employeeService->update($request, $employee);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $employee
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(User $employee): RedirectResponse
    {
        $this->employeeService->destroy($employee);
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
        $this->employeeService->import($request);
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
        return $this->employeeService->export();
    }
}
