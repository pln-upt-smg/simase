<?php

namespace App\Imports;

use App\Imports\Contract\WithDefaultEvents;
use App\Imports\Helper\HasBatchSize;
use App\Imports\Helper\HasChunkSize;
use App\Imports\Helper\HasDefaultEvents;
use App\Imports\Helper\HasDefaultSheet;
use App\Imports\Helper\HasImporter;
use App\Imports\Helper\HasMaterialResolver;
use App\Imports\Helper\HasMultipleArea;
use App\Models\ActualStock;
use App\Models\Period;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ActualStocksImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithMultipleSheets, WithChunkReading, WithBatchInserts, WithUpserts, WithEvents, WithDefaultEvents, ShouldQueue, ShouldBeUnique
{
    use HasDefaultSheet, HasDefaultEvents, HasImporter, HasChunkSize, HasBatchSize, HasMultipleArea, HasMaterialResolver;

    private int $periodId;

    public function __construct(?Period $period, ?User $user)
    {
        $this->periodId = $period?->id ?? 0;
        $this->userId = $user?->id ?? 0;
    }

    public function rules(): array
    {
        return [
            'area' => ['required', 'max:255', Rule::exists('areas', 'name')->whereNull('deleted_at')],
            'material' => ['required', 'max:255'],
            'batch' => ['required', 'max:255'],
            'materialdescription' => ['nullable', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'uom' => ['nullable', 'max:255'],
            'mtyp' => ['nullable', 'max:255']
        ];
    }

    public function uniqueBy(): string|array
    {
        return [
            'material'
        ];
    }

    /**
     * @param array $row
     * @return ActualStock|null
     * @throws ValidationException
     */
    public function model(array $row): ?ActualStock
    {
        $this->lookupArea($row);
        Validator::validate($row, [
            'material' => [Rule::exists('materials', 'code')->where('period_id', $this->periodId)->whereNull('deleted_at')]
        ]);
        return new ActualStock([
            'area_id' => $this->currentAreaId,
            'material_id' => $this->resolveMaterialId($row['material']),
            'user_id' => $this->userId,
            'batch' => Str::upper(trim($row['batch'])),
            'quantity' => $row['quantity']
        ]);
    }

    public function name(): string
    {
        return 'Actual Stock';
    }

    public function overwrite(): void
    {
        ActualStock::leftJoin('materials', 'materials.id', '=', 'actual_stocks.material_id')
            ->where('materials.period_id', $this->periodId)
            ->whereNull('actual_stocks.deleted_at')
            ->delete();
    }
}
