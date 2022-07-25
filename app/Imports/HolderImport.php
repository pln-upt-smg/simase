<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\{ShouldBeUnique, ShouldQueue};
use Illuminate\Support\Collection;
use App\Imports\Contracts\WithDefaultEvents;
use App\Imports\Helpers\{
    HasBatchSize,
    HasChunkSize,
    HasDefaultEvents,
    HasDefaultSheet,
    HasImporter
};
use App\Models\{Holder, User};
use Maatwebsite\Excel\Concerns\{
    Importable,
    SkipsEmptyRows,
    SkipsErrors,
    SkipsFailures,
    SkipsOnError,
    SkipsOnFailure,
    ToCollection,
    WithBatchInserts,
    WithChunkReading,
    WithEvents,
    WithHeadingRow,
    WithMultipleSheets,
    WithUpserts,
    WithValidation
};

class HolderImport implements
    ToCollection,
    SkipsOnFailure,
    SkipsOnError,
    SkipsEmptyRows,
    WithHeadingRow,
    WithMultipleSheets,
    WithChunkReading,
    WithBatchInserts,
    WithUpserts,
    WithEvents,
    WithDefaultEvents,
    WithValidation,
    ShouldQueue,
    ShouldBeUnique
{
    use Importable,
        SkipsFailures,
        SkipsErrors,
        HasDefaultSheet,
        HasDefaultEvents,
        HasImporter,
        HasChunkSize,
        HasBatchSize;

    public function __construct(?User $user)
    {
        if (is_null($user)) {
            $this->userId = 0;
        } else {
            $this->userId = $user->id;
        }
    }

    public function rules(): array
    {
        return [
            'namapemeganghak' => ['required', 'string', 'max:255'],
        ];
    }

    public function uniqueBy()
    {
        return ['namapemeganghak'];
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $this->replace($row->toArray());
        }
    }

    public function name(): string
    {
        return 'Pemegang Hak';
    }

    public function replace(array $row): void
    {
        if ($this->userId === 0) {
            return;
        }
        Holder::updateOrCreate([
            'created_by' => $this->userId,
            'name' => trim($row['namapemeganghak']),
        ]);
    }

    public function overwrite(): void
    {
        Holder::leftJoin('users', 'users.id', '=', 'holders.created_by')
            ->where('users.division_id', '=', auth()->user()->division_id ?? 0)
            ->whereNull('deleted_at')
            ->delete();
    }
}
