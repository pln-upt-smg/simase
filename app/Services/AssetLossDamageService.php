<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Exports\AssetLossDamageExport;
use App\Models\AssetLossDamage;
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

class AssetLossDamageService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(AssetLossDamage::class)
            ->select([
                'assets.id as asset_id',
                'assets.techidentno as techidentno',
                'assets.name as name',
                'assets.quantity as quantity',
                'asset_types.name as asset_type_name',
                'areas.funcloc as area_funcloc',
                'areas.name as area_name',
                'area_types.name as area_type_name',
                'users.name as user_name',
                'asset_loss_damages.id as id',
                'asset_loss_damages.note as asset_loss_damage_note',
                'asset_loss_damages.priority as asset_loss_damage_priority',
                DB::raw(
                    'date_format(asset_loss_damages.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin(
                'users',
                'users.id',
                '=',
                'asset_loss_damages.created_by'
            )
            ->leftJoin(
                'assets',
                'assets.id',
                '=',
                'asset_loss_damages.asset_id'
            )
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
                    'assets.techidentno',
                    'assets.name',
                    'assets.quantity',
                    'asset_types.name',
                    'areas.funcloc',
                    'areas.name',
                    'area_types.name',
                    'users.name',
                    'asset_loss_damages.note',
                    'asset_loss_damages.priority',
                ])
            )
            ->allowedSorts([
                'techidentno',
                'name',
                'quantity',
                'asset_type_name',
                'asset_type_uom',
                'area_funcloc',
                'area_name',
                'area_type_name',
                'user_name',
                'asset_loss_damage_note',
                'asset_loss_damage_priority',
                'update_date',
            ])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table
            ->addSearchRows([
                'assets.techidentno' => 'Techidentno',
                'assets.name' => 'Nama Aset',
                'assets.quantity' => 'Kuantitas',
                'asset_types.name' => 'Tipe Aset',
                'areas.funcloc' => 'Funcloc',
                'areas.name' => 'Area',
                'area_types.name' => 'Tipe Area',
                'users.name' => 'Pelapor',
                'asset_loss_damages.note' => 'Keterangan',
                'asset_loss_damages.priority' => 'Prioritas',
            ])
            ->addColumns([
                'techidentno' => 'Techidentno',
                'name' => 'Nama Aset',
                'quantity' => 'Kuantitas',
                'asset_type_name' => 'Tipe Aset',
                'asset_type_uom' => 'UoM',
                'area_funcloc' => 'Funcloc',
                'area_name' => 'Area',
                'area_type_name' => 'Tipe Area',
                'user_name' => 'Pelapor',
                'asset_loss_damage_note' => 'Keterangan',
                'asset_loss_damage_priority' => 'Prioritas',
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
                'asset.id' => [
                    'required',
                    'integer',
                    Rule::exists('assets', 'id')->whereNull('deleted_at'),
                ],
                'note' => ['nullable', 'string'],
                'priority' => ['required', 'numeric', 'min:1', 'max:3'],
            ],
            [],
            [
                'asset.id' => 'Aset',
                'note' => 'Keterangan',
                'priority' => 'Prioritas',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            AssetLossDamage::create([
                'created_by' => $user->id,
                'asset_id' => (int) $request->asset['id'],
                'note' => $request->note,
                'priority' => (int) $request->priority,
            ]);
            $user->notify(
                new DataStored('Laporan Kehilangan Aset', $request->name)
            );
        }
    }

    /**
     * @param Request $request
     * @param AssetLossDamage $assetSubmission
     * @throws Throwable
     */
    public function update(
        Request $request,
        AssetLossDamage $assetSubmission
    ): void {
        $this->validate(
            $request,
            [
                'asset.id' => [
                    'required',
                    'integer',
                    Rule::exists('assets', 'id')->whereNull('deleted_at'),
                ],
                'note' => ['nullable', 'string'],
                'priority' => ['required', 'numeric', 'min:1', 'max:3'],
            ],
            [],
            [
                'asset.id' => 'Aset',
                'note' => 'Keterangan',
                'priority' => 'Prioritas',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $assetSubmission->updateOrFail([
                'asset_id' => (int) $request->asset['id'],
                'note' => $request->note,
                'priority' => (int) $request->priority,
            ]);
            $assetSubmission->save();
            $user->notify(
                new DataUpdated('Laporan Kehilangan Aset', $request->name)
            );
        }
    }

    /**
     * @param AssetLossDamage $assetSubmission
     * @throws Throwable
     */
    public function destroy(AssetLossDamage $assetSubmission): void
    {
        $data = $assetSubmission->name;
        $user = auth()->user();
        if (!is_null($user)) {
            $assetSubmission->deleteOrFail();
            $user->notify(new DataDestroyed('Laporan Kehilangan Aset', $data));
        }
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new AssetLossDamageExport($this),
            new AssetLossDamage()
        );
    }

    /**
     * @param Request $request
     * @return AssetLossDamage|null
     */
    public function resolve(Request $request): ?AssetLossDamage
    {
        if (
            $request->query('asset_loss_damage') === '0' ||
            $request->query('asset_loss_damage') === 0
        ) {
            return null;
        }
        return AssetLossDamage::where(
            'id',
            $request->query('asset_loss_damage')
                ? (int) $request->query('asset_loss_damage')
                : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return AssetLossDamage|null
     */
    public function single(Request $request): ?AssetLossDamage
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = AssetLossDamage::orderBy('assets.name')
            ->leftJoin(
                'users',
                'users.id',
                '=',
                'asset_loss_damages.created_by'
            )
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->limit(10);
        if (!is_null($request)) {
            $query = $query
                ->leftJoin(
                    'assets',
                    'assets.id',
                    '=',
                    'asset_loss_damages.asset_id'
                )
                ->whereRaw(
                    'lower(assets.name) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                )
                ->orWhereRaw(
                    'lower(assets.quantity) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                );
        }
        return $query->get();
    }
}
