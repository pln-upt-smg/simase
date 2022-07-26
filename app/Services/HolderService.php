<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\HolderImport;
use App\Exports\HolderExport;
use App\Models\Holder;
use App\Notifications\{DataDestroyed, DataStored, DataUpdated};
use App\Services\Helpers\HasValidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\{Collection, Str};
use Illuminate\Validation\{Rule, ValidationException};
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class HolderService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Holder::class)
            ->select([
                'holders.name as name',
                'users.name as user_name',
                DB::raw(
                    'date_format(holders.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'holders.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->allowedFilters(
                InertiaHelper::filterBy(['holders.name', 'users.name'])
            )
            ->allowedSorts(['name', 'user_name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'holders.name' => 'Nama Pemegang Hak',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Pemegang Hak',
                'user_name' => 'Pembuat',
                'update_date' => 'Tanggal Pembaruan',
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
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('holders', 'name')->whereNull('deleted_at'),
                ],
            ],
            [],
            [
                'name' => 'Nama Pemegang Hak',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            Holder::create([
                'created_by' => $user->id,
                'name' => $request->name,
            ]);
            $user->notify(new DataStored('Pemegang Hak', $request->name));
        }
    }

    /**
     * @param Request $request
     * @param Holder $holder
     * @throws Throwable
     */
    public function update(Request $request, Holder $holder): void
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('holders', 'name')
                        ->ignore($holder->id)
                        ->whereNull('deleted_at'),
                ],
            ],
            [],
            [
                'name' => 'Nama Pemegang Hak',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $holder->updateOrFail([
                'name' => $request->name,
            ]);
            $holder->save();
            $user->notify(new DataUpdated('Pemegang Hak', $request->name));
        }
    }

    /**
     * @param Holder $holder
     * @throws Throwable
     */
    public function destroy(Holder $holder): void
    {
        $data = $holder->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $holder->deleteOrFail();
            $user->notify(new DataDestroyed('Pemegang Hak', $data));
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
            new HolderImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new HolderExport($this),
            new Holder()
        );
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1Pgs39WeS51rGUE_2YyMMzMOH9xBxhOmGESaZyYWZQrU/edit?usp=sharing';
    }

    /**
     * @param Request $request
     * @return Holder|null
     */
    public function resolve(Request $request): ?Holder
    {
        if (
            $request->query('holder') === '0' ||
            $request->query('holder') === 0
        ) {
            return null;
        }
        return Holder::where(
            'id',
            $request->query('holder') ? (int) $request->query('holder') : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return Holder|null
     */
    public function single(Request $request): ?Holder
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = Holder::orderBy('holders.name')
            ->select([
                'holders.name as name',
                'users.name as user_name',
                DB::raw(
                    'date_format(holders.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'holders.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->limit(10);
        if (!is_null($request)) {
            $query = $query->whereRaw(
                'lower(holders.name) like "%?%"',
                Str::lower(trim($request->query('q') ?? ''))
            );
        }
        return $query->get();
    }
}
