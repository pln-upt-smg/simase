<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Imports\AssetImport;
use App\Exports\AssetExport;
use App\Models\Asset;
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

class AssetService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Asset::class)
            ->select([
                'assets.id as id',
                'assets.name as name',
                'assets.quantity as quantity',
                'asset_types.id as asset_type_id',
                'asset_types.name as asset_type_name',
                'areas.id as area_id',
                'areas.code as area_code',
                'areas.name as area_name',
                'area_types.name as area_type_name',
                'users.name as user_name',
                DB::raw(
                    'date_format(assets.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'assets.created_by')
            ->leftJoin(
                'asset_types',
                'asset_types.id',
                '=',
                'assets.asset_type_id'
            )
            ->leftJoin('areas', 'areas.id', '=', 'assets.area_id')
            ->leftJoin('area_types', 'area_types.id', '=', 'areas.area_type_id')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->defaultSort('assets.name')
            ->allowedFilters(
                InertiaHelper::filterBy([
                    'assets.name',
                    'assets.quantity',
                    'asset_types.name',
                    'areas.name',
                    'area_types.name',
                    'users.name',
                ])
            )
            ->allowedSorts([
                'name',
                'quantity',
                'asset_type_name',
                'asset_type_uom',
                'area_name',
                'area_type_name',
                'user_name',
                'update_date',
            ])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'assets.name' => 'Nama Aset',
                'assets.quantity' => 'Kuantitas',
                'asset_types.name' => 'Tipe Aset',
                'areas.name' => 'Asset',
                'area_types.name' => 'Tipe Asset',
                'users.name' => 'Pembuat',
            ])
            ->addColumns([
                'name' => 'Nama Aset',
                'quantity' => 'Kuantitas',
                'asset_type_name' => 'Tipe Aset',
                'asset_type_uom' => 'UoM',
                'area_name' => 'Asset',
                'area_type_name' => 'Tipe Asset',
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
                'area.id' => [
                    'required',
                    'integer',
                    Rule::exists('areas', 'id')->whereNull('deleted_at'),
                ],
                'type' => [
                    'required',
                    'integer',
                    Rule::exists('asset_types', 'id')->whereNull('deleted_at'),
                ],
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('assets', 'name')->whereNull('deleted_at'),
                ],
                'quantity' => ['required', 'numeric'],
            ],
            [],
            [
                'area.id' => 'Area',
                'type' => 'Tipe Aset',
                'name' => 'Nama Aset',
                'quantity' => 'Kuantitas',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            Asset::create([
                'created_by' => $user->id,
                'area_id' => (int) $request->area['id'],
                'asset_type_id' => (int) $request->type,
                'name' => $request->name,
                'quantity' => (int) $request->quantity,
            ]);
            $user->notify(new DataStored('Aset', $request->name));
        }
    }

    /**
     * @param Request $request
     * @param Asset $asset
     * @throws Throwable
     */
    public function update(Request $request, Asset $asset): void
    {
        $this->validate(
            $request,
            [
                'area.id' => [
                    'required',
                    'integer',
                    Rule::exists('areas', 'id')->whereNull('deleted_at'),
                ],
                'type' => [
                    'required',
                    'integer',
                    Rule::exists('asset_types', 'id')->whereNull('deleted_at'),
                ],
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('assets', 'name')
                        ->ignore($asset->id)
                        ->whereNull('deleted_at'),
                ],
                'quantity' => ['required', 'numeric'],
            ],
            [],
            [
                'area.id' => 'Area',
                'type' => 'Tipe Aset',
                'name' => 'Nama Aset',
                'quantity' => 'Kuantitas',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $asset->updateOrFail([
                'area_id' => (int) $request->area['id'],
                'asset_type_id' => (int) $request->type,
                'name' => $request->name,
                'quantity' => (int) $request->quantity,
            ]);
            $asset->save();
            $user->notify(new DataUpdated('Aset', $request->name));
        }
    }

    /**
     * @param Asset $asset
     * @throws Throwable
     */
    public function destroy(Asset $asset): void
    {
        $data = $asset->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $asset->deleteOrFail();
            $user->notify(new DataDestroyed('Aset', $data));
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
            new AssetImport(auth()->user())
        );
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new AssetExport($this),
            new Asset()
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
     * @return Asset|null
     */
    public function resolve(Request $request): ?Asset
    {
        if (
            $request->query('asset') === '0' ||
            $request->query('asset') === 0
        ) {
            return null;
        }
        return Asset::where(
            'id',
            $request->query('asset') ? (int) $request->query('asset') : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return Asset|null
     */
    public function single(Request $request): ?Asset
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = Asset::orderBy('assets.name')
            ->select([
                'assets.id as id',
                'assets.name as name',
                'assets.quantity as quantity',
                'asset_types.id as asset_type_id',
                'asset_types.name as asset_type_name',
                'areas.id as area_id',
                'areas.code as area_code',
                'areas.name as area_name',
                'area_types.name as area_type_name',
                'users.name as user_name',
            ])
            ->leftJoin('users', 'users.id', '=', 'assets.created_by')
            ->leftJoin(
                'asset_types',
                'asset_types.id',
                '=',
                'assets.asset_type_id'
            )
            ->leftJoin('areas', 'areas.id', '=', 'assets.area_id')
            ->leftJoin('area_types', 'area_types.id', '=', 'areas.area_type_id')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->limit(10);
        if (!is_null($request)) {
            $filter = Str::lower(trim($request->query('q') ?? ''));
            $query = $query->where(function ($query) use ($filter) {
                $query
                    ->orWhereRaw("lower(assets.name) like (?)", ["%{$filter}%"])
                    ->orWhereRaw("lower(asset_types.name) like (?)", [
                        "%{$filter}%",
                    ])
                    ->orWhereRaw("lower(areas.name) like (?)", ["%{$filter}%"]);
            });
        }
        return $query->get();
    }
}
