<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\UrbanVillageImport;
use App\Exports\UrbanVillageExport;
use App\Models\UrbanVillage;
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

class UrbanVillageService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(UrbanVillage::class)
            ->select([
                'users.name as user_name',
                DB::raw(
                    'date_format(urban_villages.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'urban_villages.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->allowedFilters(
                InertiaHelper::filterBy(['urban_villages.name', 'users.name'])
            )
            ->allowedSorts(['name', 'user_name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'urban_villages.name' => 'Nama Kelurahan',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Kelurahan',
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
                    Rule::unique('urban_villages', 'name')->whereNull(
                        'deleted_at'
                    ),
                ],
            ],
            [
                'name' => 'Nama Kelurahan',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            UrbanVillage::create([
                'created_by' => $user->id,
                'name' => $request->name,
            ]);
            $user->notify(new DataStored('Kelurahan', $request->name));
        }
    }

    /**
     * @param Request $request
     * @param UrbanVillage $urbanVillage
     * @throws Throwable
     */
    public function update(Request $request, UrbanVillage $urbanVillage): void
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('urban_villages', 'name')
                        ->ignore($urbanVillage->id)
                        ->whereNull('deleted_at'),
                ],
            ],
            [
                'name' => 'Nama Kelurahan',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $urbanVillage->updateOrFail([
                'name' => $request->name,
            ]);
            $urbanVillage->save();
            $user->notify(new DataUpdated('Kelurahan', $request->name));
        }
    }

    /**
     * @param UrbanVillage $urbanVillage
     * @throws Throwable
     */
    public function destroy(UrbanVillage $urbanVillage): void
    {
        $data = $urbanVillage->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $urbanVillage->deleteOrFail();
            $user->notify(new DataDestroyed('Kelurahan', $data));
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
            new UrbanVillageImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new UrbanVillageExport($this),
            new UrbanVillage()
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
     * @return UrbanVillage|null
     */
    public function resolve(Request $request): ?UrbanVillage
    {
        if (
            $request->query('urban_village') === '0' ||
            $request->query('urban_village') === 0
        ) {
            return null;
        }
        return UrbanVillage::where(
            'id',
            $request->query('urban_village')
                ? (int) $request->query('urban_village')
                : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return UrbanVillage|null
     */
    public function single(Request $request): ?UrbanVillage
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = UrbanVillage::orderBy('urban_villages.name')
            ->leftJoin('users', 'users.id', '=', 'urban_villages.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->limit(10);
        if (!is_null($request)) {
            $query = $query->whereRaw(
                'lower(urban_villages.name) like "%?%"',
                Str::lower(trim($request->query('q') ?? ''))
            );
        }
        return $query->get();
    }
}
