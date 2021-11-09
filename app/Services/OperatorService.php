<?php

namespace App\Services;

use App\Exports\OperatorsExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\OperatorsImport;
use App\Models\Role;
use App\Models\User;
use App\Notifications\DataDestroyed;
use App\Notifications\DataStored;
use App\Notifications\DataUpdated;
use App\Rules\IsValidDigit;
use App\Rules\IsValidPhone;
use App\Services\Helper\HasValidator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Rules\Password;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class OperatorService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(User::class)
            ->select([
                'users.id as id',
                'users.name as name',
                'users.phone as phone',
                'users.nip as nip',
                'roles.name as role'
            ])
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.id', '<>', auth()->user()?->id ?? 0)
            ->whereNull(['users.deleted_at', 'roles.deleted_at'])
            ->defaultSort('users.name')
            ->allowedFilters(InertiaHelper::filterBy([
                'users.name',
                'users.phone',
                'users.nip',
                'roles.name'
            ]))
            ->allowedSorts([
                'name',
                'phone',
                'nip',
                'role'
            ])
            ->paginate()
            ->withQueryString();
    }

    /**
     * @param InertiaTable $table
     * @return InertiaTable
     */
    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table->addSearchRows([
            'users.name' => 'Nama Pegawai',
            'users.phone' => 'Nomor Telepon',
            'users.nip' => 'Nomor Induk Pegawai',
            'roles.name' => 'Peran'
        ])->addColumns([
            'name' => 'Nama Pegawai',
            'phone' => 'Nomor Telepon',
            'nip' => 'Nomor Induk Pegawai',
            'role' => 'Peran',
            'action' => 'Aksi'
        ]);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function store(Request $request): void
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->whereNull('deleted_at'), new IsValidPhone],
            'nip' => ['required', 'numeric', new IsValidDigit(6), Rule::unique('users', 'nip')->whereNull('deleted_at')],
            'password' => ['required', 'string', (new Password)->length(6), 'confirmed'],
            'role' => ['required', 'integer', Rule::exists('roles', 'id')->whereNull('deleted_at')]
        ], attributes: [
            'name' => 'Nama Pegawai',
            'phone' => 'Nomor Telepon',
            'nip' => 'Nomor Induk Pegawai',
            'password' => 'Kata Sandi',
            'role' => 'Peran'
        ]);
        User::create([
            'role_id' => (int)$request->role,
            'name' => Str::title($request->name),
            'phone' => $request->phone,
            'nip' => $request->nip,
            'password' => Hash::make($request->password)
        ]);
        auth()->user()?->notify(new DataStored('Pegawai', $request->nip));
    }

    /**
     * @param Request $request
     * @param User $operator
     * @throws Throwable
     */
    public function update(Request $request, User $operator): void
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($operator->id)->whereNull('deleted_at'), new IsValidPhone],
            'nip' => ['required', 'numeric', new IsValidDigit(6), Rule::unique('users', 'nip')->ignore($operator->id)->whereNull('deleted_at')],
            'password' => ['required', 'string', (new Password)->length(6), 'confirmed'],
            'role' => ['required', 'integer', Rule::exists('roles', 'id')->whereNull('deleted_at')]
        ], attributes: [
            'name' => 'Nama Pegawai',
            'phone' => 'Nomor Telepon',
            'nip' => 'Nomor Induk Pegawai',
            'password' => 'Kata Sandi',
            'role' => 'Peran'
        ]);
        $operator->updateOrFail([
            'role_id' => (int)$request->role,
            'name' => Str::title($request->name),
            'phone' => $request->phone,
            'nip' => $request->nip,
            'password' => Hash::make($request->password)
        ]);
        $operator->save();
        auth()->user()?->notify(new DataUpdated('Pegawai', $request->nip));
    }

    /**
     * @param User $operator
     * @throws Throwable
     */
    public function destroy(User $operator): void
    {
        $data = $operator->nip;
        $operator->deleteOrFail();
        auth()->user()?->notify(new DataDestroyed('Pegawai', $data));
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        MediaHelper::importSpreadsheet($request, new OperatorsImport(auth()->user()));
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new OperatorsExport($this), 'pegawai');
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
        return User::leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('roles.id', Role::operator()?->id ?? 2)
            ->whereNull(['users.deleted_at', 'roles.deleted_at'])
            ->orderBy('users.name')
            ->get()
            ->load('role');
    }
}
