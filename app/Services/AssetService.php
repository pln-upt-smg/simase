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
                'asset_types.name as asset_type_name',
                'asset_types.uom as asset_type_uom',
                'areas.name as area_name',
                'area_types.name as area_type_name',
                DB::raw(
                    'date_format(assets.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin(
                'asset_types',
                'asset_types.id',
                '=',
                'assets.asset_type_id'
            )
            ->leftJoin('areas', 'areas.id', '=', 'assets.area_id')
            ->leftJoin('area_types', 'area_types.id', '=', 'areas.area_type_id')
            ->whereNull('assets.deleted_at')
            ->defaultSort('assets.name')
            ->allowedFilters(
                InertiaHelper::filterBy([
                    'assets.name',
                    'assets.quantity',
                    'asset_types.name',
                    'asset_types.uom',
                    'areas.name',
                    'area_types.name',
                ])
            )
            ->allowedSorts([
                'name',
                'quantity',
                'asset_type_name',
                'asset_type_uom',
                'area_name',
                'area_type_name',
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
                'asset_types.uom' => 'UoM',
                'areas.name' => 'Asset',
                'area_types.name' => 'Tipe Asset',
            ])
            ->addColumns([
                'name' => 'Nama Aset',
                'quantity' => 'Kuantitas',
                'asset_type_name' => 'Tipe Aset',
                'asset_type_uom' => 'UoM',
                'area_name' => 'Asset',
                'area_type_name' => 'Tipe Asset',
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
                'area' => [
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
            [
                'area' => 'Asset',
                'type' => 'Tipe Aset',
                'name' => 'Nama Aset',
                'quantity' => 'Kuantitas',
            ]
        );
        Asset::create([
            'area_id' => (int) $request->area,
            'asset_type_id' => (int) $request->type,
            'name' => Str::title($request->name),
            'quantity' => (int) $request->quantity,
        ]);
        $user = auth()->user();
        if (!is_null($user)) {
            $user->notify(new DataStored('Aset', Str::title($request->name)));
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
                'area' => [
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
            [
                'area' => 'Area',
                'type' => 'Tipe Asset',
                'name' => 'Nama Asset',
                'quantity' => 'Kuantitas',
            ]
        );
        $asset->updateOrFail([
            'area_id' => (int) $request->area,
            'asset_type_id' => (int) $request->type,
            'name' => Str::title($request->name),
            'quantity' => (int) $request->quantity,
        ]);
        $asset->save();
        $user = auth()->user();
        if (!is_null($user)) {
            $user->notify(new DataUpdated('Aset', Str::title($request->name)));
        }
    }

    /**
     * @param Asset $asset
     * @throws Throwable
     */
    public function destroy(Asset $asset): void
    {
        $data = $asset->name;
        $asset->deleteOrFail();
        $user = auth()->user();
        if (!is_null($user)) {
            $user->notify(new DataDestroyed('Aset', Str::title($data)));
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
        )
            ->whereNull('deleted_at')
            ->first();
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
        $query = Asset::orderBy('name')->whereNull('deleted_at');
        if (!is_null($request)) {
            $query = $query
                ->whereRaw(
                    'lower(name) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                )
                ->orWhereRaw(
                    'lower(quantity) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                )
                ->limit(10);
        }
        return $query->get();
    }
}
