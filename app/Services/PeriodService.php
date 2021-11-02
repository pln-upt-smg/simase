<?php

namespace App\Services;

use App\Http\Helper\InertiaHelper;
use App\Models\Period;
use App\Services\Helper\HasValidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class PeriodService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Period::class)
            ->select([
                'periods.id as id',
                'periods.name as name'
            ])
            ->whereNull('periods.deleted_at')
            ->defaultSort('periods.name')
            ->allowedFilters(InertiaHelper::filterBy(['periods.name']))
            ->allowedSorts(['name'])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table->addSearchRows([
            'periods.name' => 'Nama Periode'
        ])->addColumns([
            'name' => 'Nama Periode',
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
            'name' => ['required', 'string', 'max:255', Rule::unique('periods')->whereNull('deleted_at')]
        ], attributes: [
            'name' => 'Nama Periode'
        ]);
        Period::create([
            'name' => Str::title($request->name)
        ]);
    }

    /**
     * @param Request $request
     * @param Period $period
     * @throws Throwable
     */
    public function update(Request $request, Period $period): void
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255', Rule::unique('periods')->ignore($period->id)->whereNull('deleted_at')]
        ], attributes: [
            'name' => 'Nama Periode'
        ]);
        $period->updateOrFail([
            'name' => Str::title($request->name)
        ]);
        $period->save();
    }

    /**
     * @param Period $period
     * @throws Throwable
     */
    public function destroy(Period $period): void
    {
        $period->deleteOrFail();
    }

    /**
     * @param Request $request
     * @return Period|null
     */
    public function resolve(Request $request): ?Period
    {
        if ($request->query('period') === '0' || $request->query('period') === 0) {
            return null;
        }
        return Period::where('id', $request->query('period') ? (int)$request->query('period') : 0)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return Period::orderBy('name')->whereNull('deleted_at')->get();
    }
}
