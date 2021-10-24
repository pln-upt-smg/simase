<?php

namespace App\Http\Controllers\Administrator;

use App\Exports\OperatorsExport;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\ResponseFactory;
use Laravel\Fortify\Rules\Password;
use Maatwebsite\Excel\Facades\Excel;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
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
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('name', 'LIKE', "%$value%")
                    ->orWhere('phone', 'LIKE', "%$value%")
                    ->orWhere('nip', 'LIKE', "%$value%");
            });
        });
        $users = QueryBuilder::for(User::class)
            ->where('role_id', '=', Role::operator()?->id ?? 2)
            ->defaultSort('name')
            ->allowedSorts(['name', 'phone', 'nip'])
            ->allowedFilters(['role_id', $globalSearch])
            ->paginate()
            ->withQueryString();
        return inertia('Administrator/Operators/Index', [
            'users' => $users,
        ])->table(function (InertiaTable $table) {
            $table->addSearchRows([
                'name' => 'Nama',
                'phone' => 'Nomor Telepon',
                'nip' => 'NIP',
                'role' => 'Peran'
            ])->addFilter('role_id', 'Peran', [
                2 => 'Operator'
            ])->addColumns([
                'name' => 'Nama',
                'phone' => 'Nomor Telepon',
                'nip' => 'NIP',
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
            'nip' => ['required', 'string', 'min:6', 'max:255', 'unique:users'],
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
            'nip' => ['required', 'string', 'min:6', 'max:255', Rule::unique('users')->ignore($operator->id)],
            'password' => ['required', 'string', (new Password)->length(6), 'confirmed'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024']
        ])->validate();
        $operator->updateOrFail([
            'role_id' => Role::operator()?->id ?? 2,
            'name' => $request->name,
            'phone' => $request->phone,
            'nip' => $request->nip,
            'password' => Hash::make($request->password)
        ]);
        if (isset($request['photo'])) {
            $operator->updateProfilePhoto($request['photo']);
        }
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
     * @return void
     * @throws ValidationException
     */
    public function import(Request $request): void
    {
        Validator::make($request->all(), [
            'file' => ['required', 'mimes:xls,xlsx,csv', 'max:51200']
        ])->validate();
    }

    /**
     * Export the resource to specified file.
     *
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return Excel::download(new OperatorsExport, 'operators.xlsx');
    }
}
