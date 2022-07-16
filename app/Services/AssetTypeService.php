<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\AssetTypeImport;
use App\Exports\AssetTypeExport;
use App\Models\AssetType;
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

class AssetTypeService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(AssetType::class)
            ->select([
                'asset_types.id as id',
                'asset_types.name as name',
                'asset_types.uom as uom',
                'users.name as user_name',
                DB::raw(
                    'date_format(asset_types.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'asset_types.created_by')
            ->whereNull('asset_types.deleted_at')
            ->defaultSort('asset_types.name')
            ->allowedFilters(
                InertiaHelper::filterBy([
                    'asset_types.name',
                    'asset_types.uom',
                    'users.name',
                ])
            )
            ->allowedSorts(['name', 'uom', 'user_name', 'update_date'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'asset_types.name' => 'Nama Tipe Aset',
                'asset_types.uom' => 'UoM',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Tipe Aset',
                'uom' => 'UoM',
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
                    Rule::unique('asset_types', 'name')->whereNull(
                        'deleted_at'
                    ),
                ],
                'uom' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'Nama Tipe Aset',
                'uom' => 'UoM',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            AssetType::create([
                'created_by' => $user->id,
                'name' => Str::title($request->name),
                'uom' => $request->uom,
            ]);
            $user->notify(
                new DataStored('Tipe Aset', Str::title($request->name))
            );
        }
    }

    /**
     * @param Request $request
     * @param AssetType $assetType
     * @throws Throwable
     */
    public function update(Request $request, AssetType $assetType): void
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('asset_types', 'name')
                        ->ignore($assetType->id)
                        ->whereNull('deleted_at'),
                ],
                'uom' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'Nama Tipe Aset',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $assetType->updateOrFail([
                'name' => Str::title($request->name),
                'uom' => $request->uom,
            ]);
            $assetType->save();
            $user->notify(
                new DataUpdated('Tipe Aset', Str::title($request->name))
            );
        }
    }

    /**
     * @param AssetType $assetType
     * @throws Throwable
     */
    public function destroy(AssetType $assetType): void
    {
        $data = $assetType->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $assetType->deleteOrFail();
            $user->notify(new DataDestroyed('Tipe Aset', Str::title($data)));
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
            new AssetTypeImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new AssetTypeExport($this),
            new AssetType()
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
     * @return AssetType|null
     */
    public function resolve(Request $request): ?AssetType
    {
        if (
            $request->query('asset_types') === '0' ||
            $request->query('asset_types') === 0
        ) {
            return null;
        }
        return AssetType::where(
            'id',
            $request->query('asset_types')
                ? (int) $request->query('asset_types')
                : 0
        )
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * @param Request $request
     * @return AssetType|null
     */
    public function single(Request $request): ?AssetType
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = AssetType::orderBy('name')->whereNull('deleted_at');
        if (!is_null($request)) {
            $query = $query
                ->whereRaw(
                    'lower(name) like "%?%"',
                    'lower(uom) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                )
                ->limit(10);
        }
        return $query->get();
    }
}
