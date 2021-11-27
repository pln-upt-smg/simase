<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\BookStock;
use App\Services\AreaService;
use App\Services\BookStockService;
use App\Services\PeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BookStockController extends Controller
{
	/**
	 * @var BookStockService
	 */
	private BookStockService $bookStockService;

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
	 * @param BookStockService $bookStockService
	 * @param AreaService $areaService
	 * @param PeriodService $periodService
	 */
	public function __construct(BookStockService $bookStockService, AreaService $areaService, PeriodService $periodService)
	{
		$this->bookStockService = $bookStockService;
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
		return inertia('Administrator/Stocks/Books/Index', [
			'area' => $area,
			'period' => $period,
			'areas' => $this->areaService->collection()->toArray(),
			'periods' => $this->periodService->collection()->toArray(),
			'stocks' => $this->bookStockService->tableData($area, $period),
			'template' => $this->bookStockService->template()
		])->table(function (InertiaTable $table) {
			$this->bookStockService->tableMeta($table);
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
		$this->bookStockService->store($request);
		return back();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param BookStock $book
	 * @return RedirectResponse
	 * @throws Throwable
	 */
	public function update(Request $request, BookStock $book): RedirectResponse
	{
		$this->bookStockService->update($request, $book);
		return back();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param BookStock $book
	 * @return RedirectResponse
	 * @throws Throwable
	 */
	public function destroy(BookStock $book): RedirectResponse
	{
		$this->bookStockService->destroy($book);
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
		$this->bookStockService->import($request);
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
		return $this->bookStockService->export($request);
	}

	/**
	 * Truncate all resource from storage.
	 *
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function truncate(Request $request): RedirectResponse
	{
		$this->bookStockService->truncate($this->areaService->resolve($request), $this->periodService->resolve($request));
		return back();
	}
}
