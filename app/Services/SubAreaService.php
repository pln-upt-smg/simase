<?php

namespace App\Services;

use App\Exports\SubAreasExport;
use App\Http\Helper\InertiaHelper;
use App\Http\Helper\MediaHelper;
use App\Imports\SubAreasImport;
use App\Models\Area;
use App\Models\SubArea;
use App\Notifications\DataDestroyed;
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

class SubAreaService
{
	use HasValidator;

	/**
	 * @return LengthAwarePaginator
	 */
	public function tableData(): LengthAwarePaginator
	{
		return QueryBuilder::for(SubArea::class)
			->select([
				'sub_areas.id as id',
				'sub_areas.name as name',
				'areas.sloc as sloc'
			])
			->leftJoin('areas', 'areas.id', '=', 'sub_areas.area_id')
			->whereNull(['sub_areas.deleted_at', 'areas.deleted_at'])
			->defaultSort('sub_areas.name')
			->allowedFilters(InertiaHelper::filterBy([
				'sub_areas.name',
				'areas.sloc'
			]))
			->allowedSorts([
				'name',
				'sloc'
			])
			->paginate()
			->withQueryString();
	}

	public function tableMeta(InertiaTable $table): InertiaTable
	{
		return $table->addSearchRows([
			'sub_areas.name' => 'Sub Area',
			'areas.sloc' => 'SLoc'
		])->addColumns([
			'name' => 'Sub Area',
			'sloc' => 'SLoc',
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
			'name' => ['required', 'string', 'max:255', Rule::unique('sub_areas', 'name')->whereNull('deleted_at')],
			'sloc' => ['required', 'numeric', Rule::exists('areas', 'sloc')->whereNull('deleted_at')]
		], attributes: [
			'name' => 'Sub Area',
			'sloc' => 'SLoc'
		]);
		SubArea::create([
			'area_id' => Area::whereRaw('lower(sloc) = lower(?)', $request->sloc)->whereNull('deleted_at')->first()?->id ?? 0,
			'name' => Str::title($request->name)
		]);
		auth()->user()?->notify(new DataStored('SubArea', Str::title($request->name)));
	}

	/**
	 * @param Request $request
	 * @param SubArea $subArea
	 * @throws Throwable
	 */
	public function update(Request $request, SubArea $subArea): void
	{
		$this->validate($request, [
			'name' => ['required', 'string', 'max:255', Rule::unique('sub_areas', 'name')->ignore($subArea->id)->whereNull('deleted_at')],
			'sloc' => ['required', 'numeric', Rule::exists('areas', 'sloc')->whereNull('deleted_at')]
		], attributes: [
			'name' => 'Sub Area',
			'sloc' => 'SLoc'
		]);
		$subArea->updateOrFail([
			'area_id' => Area::whereRaw('lower(sloc) = lower(?)', $request->sloc)->whereNull('deleted_at')->first()?->id ?? 0,
			'name' => Str::title($request->name)
		]);
		$subArea->save();
		auth()->user()?->notify(new DataUpdated('SubArea', Str::title($request->name)));
	}

	/**
	 * @param SubArea $subArea
	 * @throws Throwable
	 */
	public function destroy(SubArea $subArea): void
	{
		$data = $subArea->name;
		$subArea->deleteOrFail();
		auth()->user()?->notify(new DataDestroyed('SubArea', Str::title($data)));
	}

	/**
	 * @param Request $request
	 * @throws Throwable
	 */
	public function import(Request $request): void
	{
		MediaHelper::importSpreadsheet($request, new SubAreasImport(auth()->user()));
	}

	/**
	 * @return BinaryFileResponse
	 * @throws Throwable
	 */
	public function export(): BinaryFileResponse
	{
		return MediaHelper::exportSpreadsheet(new SubAreasExport($this), new SubArea);
	}

	/**
	 * @return string
	 */
	public function template(): string
	{
		return 'https://docs.google.com/spreadsheets/d/1royDJ1XwUdsu5tOjYOWrOpvQuj7mZt1D8M0G4BnPlSA/edit?usp=sharing';
	}

	/**
	 * @param Request $request
	 * @return SubArea|null
	 */
	public function resolve(Request $request): ?SubArea
	{
		if ($request->query('subarea') === '0' || $request->query('subarea') === 0) {
			return null;
		}
		return SubArea::where('id', $request->query('subarea') ? (int)$request->query('subarea') : 0)->whereNull('deleted_at')->first()?->load('area');
	}

	/**
	 * @param Request|null $request
	 * @return SubArea|null
	 */
	public function single(?Request $request = null): ?SubArea
	{
		return $this->collection($request)->first();
	}

	/**
	 * @param Request|null $request
	 * @return Collection
	 */
	public function collection(?Request $request = null): Collection
	{
		$query = SubArea::select([
			'sub_areas.id as id',
			'sub_areas.name as name',
			'areas.name as area_name',
			'areas.sloc as sloc'
		])
			->leftJoin('areas', 'areas.id', '=', 'sub_areas.area_id')
			->orderBy('sub_areas.name')
			->whereNull(['sub_areas.deleted_at', 'areas.deleted_at']);
		if (!is_null($request)) {
			$query = $query
				->whereRaw('lower(areas.name) like ?', '%' . Str::lower($request->query('q') ?? '') . '%')
				->orWhereRaw('lower(sub_areas.name) like ?', '%' . Str::lower($request->query('q') ?? '') . '%')
				->orWhereRaw('lower(areas.sloc) like ?', Str::lower($request->query('q') ?? '') . '%');
		}
		return $query->get();
	}
}
