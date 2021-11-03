<?php

namespace App\Services;

use App\Exports\AreasExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\AreasImport;
use App\Models\Area;
use App\Notifications\DataDestroyed;
use App\Notifications\DataImported;
use App\Notifications\DataStored;
use App\Notifications\DataUpdated;
use App\Services\Helper\HasValidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
            ->defaultSort('areas.name')
            ->allowedFilters(InertiaHelper::filterBy(['areas.name']))
            ->allowedSorts(['name'])
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
            'name' => ['required', 'string', 'max:255', Rule::unique('areas', 'name')->whereNull('deleted_at')]
        ], attributes: [
            'name' => 'Nama Area'
        ]);
        Area::create([
            'name' => Str::title($request->name)
        ]);
        auth()->user()?->notify(new DataStored('Area', Str::title($request->name)));
    }

    /**
     * @param Request $request
     * @param Area $area
     * @throws Throwable
     */
    public function update(Request $request, Area $area): void
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255', Rule::unique('areas', 'name')->ignore($area->id)->whereNull('deleted_at')]
        ], attributes: [
            'name' => 'Nama Area'
        ]);
        $area->updateOrFail([
            'name' => Str::title($request->name)
        ]);
        $area->save();
        auth()->user()?->notify(new DataUpdated('Area', Str::title($request->name)));
    }

    /**
     * @param Area $area
     * @throws Throwable
     */
    public function destroy(Area $area): void
    {
        $data = $area->name;
        $area->deleteOrFail();
        auth()->user()?->notify(new DataDestroyed('Area', Str::title($data)));
    }

    /**
     * @param Request $request
     * @throws Throwable
     */
    public function import(Request $request): void
    {
        $import = new AreasImport;
        MediaHelper::importSpreadsheet($request, $import);
        auth()->user()?->notify(new DataImported('Area', $import->getRowCount()));
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

    /**
     * @param Request $request
     * @return Area|null
     */
    public function resolve(Request $request): ?Area
    {
        if ($request->query('area') === '0' || $request->query('area') === 0) {
            return null;
        }
        return Area::where('id', $request->query('area') ? (int)$request->query('area') : 0)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return Area::orderBy('name')->whereNull('deleted_at')->get();
    }
}
