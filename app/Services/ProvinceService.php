<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\ProvinceImport;
use App\Exports\ProvinceExport;
use App\Models\Province;
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

class ProvinceService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Province::class)
            ->select([
                'users.name as user_name',
                DB::raw(
                    'date_format(provinces.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'provinces.created_by')
            ->allowedFilters(
                InertiaHelper::filterBy(['provinces.name', 'users.name'])
            )
            ->allowedSorts(['name', 'user_name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'provinces.name' => 'Nama Provinsi',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Provinsi',
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
                    Rule::unique('provinces', 'name')->whereNull('deleted_at'),
                ],
            ],
            [
                'name' => 'Nama Provinsi',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            Province::create([
                'created_by' => $user->id,
                'name' => $request->name,
            ]);
            $user->notify(new DataStored('Provinsi', $request->name));
        }
    }

    /**
     * @param Request $request
     * @param Province $province
     * @throws Throwable
     */
    public function update(Request $request, Province $province): void
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('provinces', 'name')
                        ->ignore($province->id)
                        ->whereNull('deleted_at'),
                ],
            ],
            [
                'name' => 'Nama Provinsi',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $province->updateOrFail([
                'name' => $request->name,
            ]);
            $province->save();
            $user->notify(new DataUpdated('Provinsi', $request->name));
        }
    }

    /**
     * @param Province $province
     * @throws Throwable
     */
    public function destroy(Province $province): void
    {
        $data = $province->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $province->deleteOrFail();
            $user->notify(new DataDestroyed('Provinsi', $data));
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
            new ProvinceImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new ProvinceExport($this),
            new Province()
        );
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1_iyLqpZbz09w22YRenD7kFuyidQJIUSf4-33jkZ8_kA/edit?usp=sharing';
    }

    /**
     * @param Request $request
     * @return Province|null
     */
    public function resolve(Request $request): ?Province
    {
        if (
            $request->query('province') === '0' ||
            $request->query('province') === 0
        ) {
            return null;
        }
        return Province::where(
            'id',
            $request->query('province') ? (int) $request->query('province') : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return Province|null
     */
    public function single(Request $request): ?Province
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = Province::orderBy('name')->limit(10);
        if (!is_null($request)) {
            $query = $query->whereRaw(
                'lower(name) like "%?%"',
                Str::lower(trim($request->query('q') ?? ''))
            );
        }
        return $query->get();
    }
}
