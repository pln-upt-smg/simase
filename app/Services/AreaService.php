<?php

namespace App\Services;

use App\Exports\AreasExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\AreasImport;
use App\Models\Area;
use App\Services\Helper\HasValidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AreaService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Area::class)
            ->select([
                'areas.id as id',
                'areas.name as name'
            ])
            ->whereNull('areas.deleted_at')
            ->defaultSort('name')
            ->allowedSorts(['name'])
            ->allowedFilters([
                'areas.name',
                InertiaHelper::searchQueryCallback('areas.name')
            ])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table->addSearchRows([
            'areas.name' => 'Nama Area'
        ])->addColumns([
            'name' => 'Nama Area',
            'action' => 'Aksi'
        ]);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function store(Request $request): void
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255', Rule::unique('areas')->whereNull('deleted_at')]
        ]);
        Area::create([
            'name' => Str::title($request->name)
        ]);
    }

    /**
     * @param Request $request
     * @param Area $area
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request, Area $area): void
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255', Rule::unique('areas')->ignore($area->id)->whereNull('deleted_at')]
        ]);
        $area->updateOrFail([
            'name' => Str::title($request->name)
        ]);
        $area->save();
    }

    /**
     * @param Area $area
     * @throws Throwable
     */
    public function destroy(Area $area): void
    {
        $area->deleteOrFail();
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        MediaHelper::importSpreadsheet($request, new AreasImport);
    }

    /**
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return MediaHelper::exportSpreadsheet(new AreasExport, new Area);
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1_iyLqpZbz09w22YRenD7kFuyidQJIUSf4-33jkZ8_kA/edit?usp=sharing';
    }
}
