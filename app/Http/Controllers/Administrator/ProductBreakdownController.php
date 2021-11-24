<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\ProductBreakdown;
use App\Services\AreaService;
use App\Services\PeriodService;
use App\Services\ProductBreakdownService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProductBreakdownController extends Controller
{
	/**
	 * @var ProductBreakdownService
	 */
	private ProductBreakdownService $productBreakdownService;

	/**
	 * @var AreaService
	 */
	private AreaService $areaService;

	/**
	 * @var PeriodService
	 */
	private PeriodService $periodService;

	/**
	 * Create a new Controller instance.
	 *
	 * @param ProductBreakdownService $productBreakdownService
	 * @param AreaService $areaService
	 * @param PeriodService $periodService
	 */
	public function __construct(ProductBreakdownService $productBreakdownService, AreaService $areaService, PeriodService $periodService)
	{
		$this->productBreakdownService = $productBreakdownService;
		$this->areaService = $areaService;
		$this->periodService = $periodService;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return Response|ResponseFactory
	 */
	public function index(Request $request): Response|ResponseFactory
	{
		$area = $this->areaService->resolve($request);
		$period = $this->periodService->resolve($request);
		return inertia('Administrator/Products/Breakdowns/Index', [
			'area' => $area,
			'period' => $period,
			'areas' => $this->areaService->collection()->toArray(),
			'periods' => $this->periodService->collection()->toArray(),
			'breakdowns' => $this->productBreakdownService->tableData($area, $period),
			'template' => $this->productBreakdownService->template()
		])->table(function (InertiaTable $table) {
			$this->productBreakdownService->tableMeta($table);
		});
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 * @throws Throwable
	 */
	public function store(Request $request): RedirectResponse
	{
		$this->productBreakdownService->store($request);
		return back();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param ProductBreakdown $breakdown
	 * @return RedirectResponse
	 * @throws Throwable
	 */
	public function update(Request $request, ProductBreakdown $breakdown): RedirectResponse
	{
		$this->productBreakdownService->update($request, $breakdown);
		return back();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param ProductBreakdown $breakdown
	 * @return RedirectResponse
	 * @throws Throwable
	 */
	public function destroy(ProductBreakdown $breakdown): RedirectResponse
	{
		$this->productBreakdownService->destroy($breakdown);
		return back();
	}

	/**
	 * Import the resource from file.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 * @throws Throwable
	 */
	public function import(Request $request): RedirectResponse
	{
		$this->productBreakdownService->import($request);
		return back();
	}

	/**
	 * Export the resource to specified file.
	 *
	 * @param Request $request
	 * @return BinaryFileResponse
	 * @throws Throwable
	 */
	public function export(Request $request): BinaryFileResponse
	{
		return $this->productBreakdownService->export($request);
	}
}
