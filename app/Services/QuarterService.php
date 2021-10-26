<?php

namespace App\Services;

use App\Http\Helper\InertiaHelper;
use App\Models\Quarter;
use App\Services\Helper\HasValidator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class QuarterService
{
    use HasValidator;

    /**
     * @return LengthAwarePaginator
     */
    public function tableData(): LengthAwarePaginator
    {
        return QueryBuilder::for(Quarter::class)
            ->select([
                'quarters.id as id',
                'quarters.name as name'
            ])
            ->whereNull('quarters.deleted_at')
            ->defaultSort('name')
            ->allowedSorts(['name'])
            ->allowedFilters([
                'quarters.name',
                InertiaHelper::searchQueryCallback('quarters.name')
            ])
            ->paginate()
            ->withQueryString();
    }

    public function tableMeta(InertiaTable $table): InertiaTable
    {
        return $table->addSearchRows([
            'quarters.name' => 'Nama Quarter'
        ])->addColumns([
            'name' => 'Nama Quarter',
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
            'name' => ['required', 'string', 'max:255', Rule::unique('quarters')->whereDeletedAt(null)]
        ]);
        Quarter::create([
            'name' => Str::title($request->name)
        ]);
    }

    /**
     * @param Request $request
     * @param Quarter $quarter
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request, Quarter $quarter): void
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255', Rule::unique('quarters')->ignore($quarter->id)->whereDeletedAt(null)]
        ]);
        $quarter->updateOrFail([
            'name' => Str::title($request->name)
        ]);
        $quarter->save();
    }

    /**
     * @param Quarter $quarter
     * @throws Throwable
     */
    public function destroy(Quarter $quarter): void
    {
        $quarter->deleteOrFail();
    }
}
