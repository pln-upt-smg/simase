<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\ResponseFactory;
use Laravel\Fortify\Rules\Password;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
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
            ->allowedFilters(['name', 'phone', 'nip', $globalSearch])
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
     * @return Response|string
     * @throws Throwable
     */
    public function store(Request $request): Response|string
    {
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'nip' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', (new Password)->length(6), 'confirmed']
        ])->validate();
        return User::create([
            'role_id' => Role::operator(),
            'name' => $request->name,
            'phone' => $request->phone,
            'nip' => $request->nip,
            'password' => Hash::make($request->password)
        ])->toJson();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return Response|string
     * @throws Throwable
     */
    public function update(Request $request, User $user): Response|string
    {
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'nip' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['required', 'string', (new Password)->length(6), 'confirmed'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024']
        ])->validate();
        $user->updateOrFail([
            'name' => $request->name,
            'phone' => $request->phone,
            'nip' => $request->nip,
            'password' => Hash::make($request->password)
        ]);
        if (isset($request['photo'])) {
            $user->updateProfilePhoto($request['photo']);
        }
        $user->save();
        return $user->refresh()->toJson();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return Response|bool
     * @throws Throwable
     */
    public function destroy(User $user): Response|bool
    {
        return $user->deleteOrFail();
    }
}
