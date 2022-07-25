<?php

namespace App\Services;

use App\Http\Helper\{InertiaHelper, MediaHelper};
use App\Exports\AssetTransferExport;
use App\Models\AssetTransfer;
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

class AssetTransferService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(AssetTransfer::class)
            ->select([
                'assets.id as asset_id',
                'assets.techidentno as techidentno',
                'assets.name as name',
                'assets.quantity as quantity',
                'asset_types.id as asset_type_id',
                'asset_types.name as asset_type_name',
                'areas.id as area_id',
                'areas.funcloc as area_funcloc',
                'areas.name as area_name',
                'area_types.name as area_type_name',
                'target_areas.id as target_area_id',
                'target_areas.funcloc as target_area_funcloc',
                'target_areas.name as target_area_name',
                'target_area_types.name as target_area_type_name',
                'users.name as user_name',
                'asset_transfers.id as id',
                'asset_transfers.note as asset_submission_note',
                'asset_transfers.quantity as asset_submission_quantity',
                'asset_transfers.priority as asset_submission_priority',
                DB::raw(
                    'date_format(asset_transfers.updated_at, "%d %b %Y") as update_date'
                ),
            ])
            ->leftJoin('users', 'users.id', '=', 'asset_transfers.created_by')
            ->leftJoin('assets', 'assets.id', '=', 'asset_transfers.asset_id')
            ->leftJoin(
                'asset_types',
                'asset_types.id',
                '=',
                'assets.asset_type_id'
            )
            ->leftJoin('areas', 'areas.id', '=', 'assets.area_id')
            ->leftJoin(
                'areas as target_areas',
                'areas.id',
                '=',
                'asset_transfers.area_id'
            )
            ->leftJoin('area_types', 'area_types.id', '=', 'areas.area_type_id')
            ->leftJoin(
                'area_types as target_area_types',
                'area_types.id',
                '=',
                'target_areas.area_type_id'
            )
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->defaultSort('assets.name')
            ->allowedFilters(
                InertiaHelper::filterBy([
                    'assets.techidentno',
                    'assets.name',
                    'assets.quantity',
                    'asset_types.name',
                    'areas.name',
                    'area_types.name',
                    'target_areas.name',
                    'target_area_types.name',
                    'users.name',
                    'asset_transfers.note',
                    'asset_transfers.quantity',
                    'asset_transfers.priority',
                ])
            )
            ->allowedSorts([
                'techidentno',
                'name',
                'quantity',
                'asset_type_name',
                'asset_type_uom',
                'area_name',
                'area_type_name',
                'target_area_name',
                'target_area_type_name',
                'user_name',
                'asset_submission_note',
                'asset_submission_quantity',
                'asset_submission_priority',
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
                'areas.name' => 'Area Asal',
                'area_types.name' => 'Tipe Area Asal',
                'target_areas.name' => 'Area Tujuan',
                'target_area_types.name' => 'Tipe Area Tujuan',
                'users.name' => 'Pelapor',
                'asset_transfers.note' => 'Keterangan',
                'asset_transfers.quantity' => 'Pengajuan Kuantitas',
                'asset_transfers.priority' => 'Prioritas',
            ])
            ->addColumns([
                'techidentno' => 'Techidentno',
                'name' => 'Nama Aset',
                'quantity' => 'Kuantitas',
                'asset_type_name' => 'Tipe Aset',
                'asset_type_uom' => 'UoM',
                'area_name' => 'Area',
                'area_type_name' => 'Tipe Area',
                'target_area_name' => 'Area Tujuan',
                'target_area_type_name' => 'Tipe Area Tujuan',
                'user_name' => 'Pelapor',
                'asset_submission_note' => 'Keterangan',
                'asset_submission_quantity' => 'Pengajuan Kuantitas',
                'asset_submission_priority' => 'Prioritas',
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
                'area.id' => [
                    'required',
                    'integer',
                    Rule::exists('areas', 'id')->whereNull('deleted_at'),
                ],
                'note' => ['nullable', 'string'],
                'quantity' => ['required', 'numeric', 'min:1'],
                'priority' => ['required', 'numeric', 'min:1', 'max:3'],
            ],
            [],
            [
                'asset.id' => 'Aset',
                'area.id' => 'Area Tujuan',
                'note' => 'Keterangan',
                'quantity' => 'Pengajuan Kuantitas',
                'priority' => 'Prioritas',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            AssetTransfer::create([
                'asset_id' => (int) $request->asset['id'],
                'area_id' => (int) $request->area['id'],
                'note' => $request->note,
                'quantity' => (int) $request->quantity,
                'priority' => (int) $request->priority,
                'created_by' => $user->id,
            ]);
            $user->notify(
                new DataStored('Laporan Transfer Aset', $request->asset['name'])
            );
        }
    }

    /**
     * @param Request $request
     * @param AssetTransfer $assetTransfer
     * @throws Throwable
     */
    public function update(Request $request, AssetTransfer $assetTransfer): void
    {
        $this->validate(
            $request,
            [
                'asset.id' => [
                    'required',
                    'integer',
                    Rule::exists('assets', 'id')->whereNull('deleted_at'),
                ],
                'area.id' => [
                    'required',
                    'integer',
                    Rule::exists('areas', 'id')->whereNull('deleted_at'),
                ],
                'note' => ['nullable', 'string'],
                'quantity' => ['required', 'numeric', 'min:1'],
                'priority' => ['required', 'numeric', 'min:1', 'max:3'],
            ],
            [],
            [
                'asset.id' => 'Aset',
                'area.id' => 'Area Tujuan',
                'note' => 'Keterangan',
                'quantity' => 'Pengajuan Kuantitas',
                'priority' => 'Prioritas',
            ]
        );
        $user = auth()->user();
        if (!is_null($user)) {
            $assetTransfer->updateOrFail([
                'asset_id' => (int) $request->asset['id'],
                'area_id' => (int) $request->area['id'],
                'note' => $request->note,
                'quantity' => (int) $request->quantity,
                'priority' => (int) $request->priority,
            ]);
            $assetTransfer->save();
            $user->notify(
                new DataUpdated(
                    'Laporan Transfer Aset',
                    $request->asset['name']
                )
            );
        }
    }

    /**
     * @param AssetTransfer $assetTransfer
     * @throws Throwable
     */
    public function destroy(AssetTransfer $assetTransfer): void
    {
        $data = $assetTransfer->asset;
        $user = auth()->user();
        if (!is_null($data) && !is_null($user)) {
            $assetTransfer->deleteOrFail();
            $user->notify(
                new DataDestroyed('Laporan Transfer Aset', $data->name)
            );
        }
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(
            new AssetTransferExport($this),
            new AssetTransfer()
        );
    }

    /**
     * @param Request $request
     * @return AssetTransfer|null
     */
    public function resolve(Request $request): ?AssetTransfer
    {
        if (
            $request->query('asset_transfer') === '0' ||
            $request->query('asset_transfer') === 0
        ) {
            return null;
        }
        return AssetTransfer::where(
            'id',
            $request->query('asset_transfer')
                ? (int) $request->query('asset_transfer')
                : 0
        )->first();
    }

    /**
     * @param Request $request
     * @return AssetTransfer|null
     */
    public function single(Request $request): ?AssetTransfer
    {
        return $this->collection($request)->first();
    }

    /**
     * @param Request|null $request
     * @return Collection
     */
    public function collection(?Request $request = null): Collection
    {
        $query = AssetTransfer::orderBy('assets.name')
            ->leftJoin('users', 'users.id', '=', 'asset_transfers.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->limit(10);
        if (!is_null($request)) {
            $query = $query
                ->leftJoin(
                    'assets',
                    'assets.id',
                    '=',
                    'asset_transfers.asset_id'
                )
                ->whereRaw(
                    'lower(assets.name) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                )
                ->orWhereRaw(
                    'lower(asset_transfers.quantity) like "%?%"',
                    Str::lower(trim($request->query('q') ?? ''))
                );
        }
        return $query->get();
    }
}
