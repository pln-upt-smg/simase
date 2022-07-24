<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\EmployeeImport;
use App\Exports\EmployeeExport;
use App\Models\User;
use App\Notifications\{DataDestroyed, DataStored, DataUpdated};
use App\Rules\{IsValidDigit, IsValidPhone};
use App\Services\Helpers\HasValidator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\{Rule, ValidationException};
use Laravel\Fortify\Rules\Password;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class EmployeeService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        $userId = 0;
        $user = auth()->user();
        if (!is_null($user)) {
            $userId = $user->id;
        }
        return QueryBuilder::for(User::class)
            ->select([
                'users.id as id',
                'users.name as name',
                'users.phone as phone',
                'users.nip as nip',
                'roles.id as role_id',
                'roles.name as role',
                'divisions.id as division_id',
                'divisions.name as division',
            ])
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->leftJoin('divisions', 'divisions.id', '=', 'users.division_id')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->where('users.id', '<>', $userId)
            ->defaultSort('users.name')
            ->allowedFilters(
                InertiaHelper::filterBy([
                    'users.name',
                    'users.phone',
                    'users.nip',
                    'roles.name',
                    'divisions.name',
                ])
            )
            ->allowedSorts(['name', 'phone', 'nip', 'role', 'division'])
            ->paginate()
            ->withQueryString();
    }

    /**
     * @param InertiaTable $table
     * @return InertiaTable
     */
    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'users.name' => 'Nama Pegawai',
                'users.phone' => 'Nomor Telepon',
                'users.nip' => 'Nomor Induk Pegawai',
                'roles.name' => 'Peran',
                'divisions.name' => 'Divisi',
            ])
            ->addColumns([
                'name' => 'Nama Pegawai',
                'phone' => 'Nomor Telepon',
                'nip' => 'Nomor Induk Pegawai',
                'role' => 'Peran',
                'division' => 'Divisi',
                'action' => 'Aksi',
            ]);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function store(Request $request): void
    {
        $this->validate(
            $request,
            [
                'name' => ['required', 'string', 'max:255'],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    new IsValidPhone(),
                    Rule::unique('users', 'phone')->whereNull('deleted_at'),
                ],
                'nip' => [
                    'required',
                    'numeric',
                    new IsValidDigit(6),
                    Rule::unique('users', 'nip')->whereNull('deleted_at'),
                ],
                'password' => [
                    'required',
                    'string',
                    (new Password())->length(6),
                    'confirmed',
                ],
                'role' => [
                    'required',
                    'integer',
                    Rule::exists('roles', 'id')->whereNull('deleted_at'),
                ],
                'division' => [
                    'required',
                    'integer',
                    Rule::exists('divisions', 'id')->whereNull('deleted_at'),
                ],
            ],
            [],
            [
                'name' => 'Nama Pegawai',
                'phone' => 'Nomor Telepon',
                'nip' => 'Nomor Induk Pegawai',
                'password' => 'Kata Sandi',
                'role' => 'Peran',
                'division' => 'Divisi',
            ]
        );
        User::create([
            'role_id' => (int) $request->role,
            'division_id' => (int) $request->division,
            'name' => $request->name,
            'phone' => $request->phone,
            'nip' => $request->nip,
            'password' => Hash::make($request->password),
        ]);
        $user = auth()->user();
        if (!is_null($user)) {
            $user->notify(new DataStored('Pegawai', $request->nip));
        }
    }

    /**
     * @param Request $request
     * @param User $employee
     * @throws Throwable
     */
    public function update(Request $request, User $employee): void
    {
        $this->validate(
            $request,
            [
                'name' => ['required', 'string', 'max:255'],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    new IsValidPhone(),
                    Rule::unique('users', 'phone')
                        ->ignore($employee->id)
                        ->whereNull('deleted_at'),
                ],
                'nip' => [
                    'required',
                    'numeric',
                    new IsValidDigit(6),
                    Rule::unique('users', 'nip')
                        ->ignore($employee->id)
                        ->whereNull('deleted_at'),
                ],
                'password' => [
                    'required',
                    'string',
                    (new Password())->length(6),
                    'confirmed',
                ],
                'role' => [
                    'required',
                    'integer',
                    Rule::exists('roles', 'id')->whereNull('deleted_at'),
                ],
                'division' => [
                    'required',
                    'integer',
                    Rule::exists('divisions', 'id')->whereNull('deleted_at'),
                ],
            ],
            [],
            [
                'name' => 'Nama Pegawai',
                'phone' => 'Nomor Telepon',
                'nip' => 'Nomor Induk Pegawai',
                'password' => 'Kata Sandi',
                'role' => 'Peran',
                'division' => 'Divisi',
            ]
        );
        $employee->updateOrFail([
            'role_id' => (int) $request->role,
            'division_id' => (int) $request->division,
            'name' => $request->name,
            'phone' => $request->phone,
            'nip' => $request->nip,
            'password' => Hash::make($request->password),
        ]);
        $employee->save();
        $user = auth()->user();
        if (!is_null($user)) {
            $user->notify(new DataUpdated('Pegawai', $request->nip));
        }
    }

    /**
     * @param User $employee
     * @throws Throwable
     */
    public function destroy(User $employee): void
    {
        $data = $employee->nip;
        $employee->deleteOrFail();
        $user = auth()->user();
        if (!is_null($user)) {
            $user->notfy(new DataDestroyed('Pegawai', $data));
        }
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        MediaHelper::importSpreadsheet(
            $request,
            new EmployeeImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new EmployeeExport($this),
            'employees'
        );
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1uOA5ear--StRXSFf_iIYVW-50daP4KmA1vOcDxIRZoo/edit?usp=sharing';
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $userId = 0;
        $user = auth()->user();
        if (!is_null($user)) {
            $userId = $user->id;
        }
        return User::leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->where('users.id', '<>', $userId)
            ->orderBy('users.name')
            ->get()
            ->load('role');
    }
}
