<?php

namespace App\Http\Controllers\Administrator;

use App\Exports\OperatorsExport;
use App\Http\Controllers\Controller;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\OperatorsImport;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\ResponseFactory;
use Laravel\Fortify\Rules\Password;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class OperatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|ResponseFactory
     */
    public function index(): \Inertia\Response|ResponseFactory
    {
        $data = QueryBuilder::for(User::class)
            ->select([
                'users.id as id',
                'users.name as name',
                'users.phone as phone',
                'users.nip as nip',
                'users.role_id as role_id',
                'roles.name as role'
            ])
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.role_id', '=', Role::operator()?->id ?? 2)
            ->defaultSort('name')
            ->allowedSorts(['name', 'phone', 'nip', 'role'])
            ->allowedFilters([
                'name',
                'phone',
                'nip',
                'role',
                'role_id',
                InertiaHelper::buildGlobalSearchQueryCallback('users.name', 'users.phone', 'users.nip', 'roles.name')
            ])
            ->paginate()
            ->withQueryString();
        return inertia('Administrator/Operators/Index', [
            'operators' => $data,
            'template' => $this->template()
        ])->table(function (InertiaTable $table) {
            $table->addSearchRows([
                'name' => 'Nama Pegawai',
                'phone' => 'Nomor Telepon',
                'nip' => 'Nomor Induk Pegawai',
                'role' => 'Peran'
            ])->addFilter('role_id', 'Peran', [
                2 => 'Operator'
            ])->addColumns([
                'name' => 'Nama Pegawai',
                'phone' => 'Nomor Telepon',
                'nip' => 'Nomor Induk Pegawai',
                'role' => 'Peran',
                'action' => 'Aksi'
            ]);
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
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'nip' => ['required', 'alpha_num', 'min:6', 'max:255', 'unique:users'],
            'password' => ['required', 'string', (new Password)->length(6), 'confirmed']
        ])->validate();
        User::create([
            'role_id' => Role::operator()?->id ?? 2,
            'name' => $request->name,
            'phone' => $request->phone,
            'nip' => $request->nip,
            'password' => Hash::make($request->password)
        ]);
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
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($operator->id)],
            'nip' => ['required', 'alpha_num', 'min:6', 'max:255', Rule::unique('users')->ignore($operator->id)],
            'password' => ['required', 'string', (new Password)->length(6), 'confirmed']
        ])->validate();
        $operator->updateOrFail([
            'role_id' => Role::operator()?->id ?? 2,
            'name' => $request->name,
            'phone' => $request->phone,
            'nip' => $request->nip,
            'password' => Hash::make($request->password)
        ]);
        $operator->save();
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
        $operator->deleteOrFail();
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
        MediaHelper::importSpreadsheet($request, new OperatorsImport);
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
        return MediaHelper::exportSpreadsheet(new OperatorsExport, 'operators');
    }

    /**
     * File URL for the resource import template
     *
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1uOA5ear--StRXSFf_iIYVW-50daP4KmA1vOcDxIRZoo/edit?usp=sharing';
    }
}
