<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OperatorController extends Controller
{
    public function index(): ResponseFactory|Response
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('name', 'LIKE', "%$value%")->orWhere('nip', 'LIKE', "%$value%");
            });
        });
        $users = QueryBuilder::for(User::class)
            ->where('role_id', '<>', Role::administrator()->id)
            ->defaultSort('name')
            ->allowedSorts(['name', 'nip'])
            ->allowedFilters(['name', 'nip', 'role_id', $globalSearch])
            ->paginate()
            ->withQueryString();
        return inertia('Administrator/Operators/Index', [
            'users' => $users,
        ])->table(function (InertiaTable $table) {
            $table->addSearchRows([
                'name' => 'Nama',
                'nip' => 'NIP'
            ])->addFilter('role_id', 'Peran', [
                2 => 'Operator'
            ])->addColumns([
                'name' => 'Nama',
                'nip' => 'NIP',
                'role' => 'Peran'
            ]);
        });
    }
}
