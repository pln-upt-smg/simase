<?php

namespace App\Http\Controllers;

use App\Services\AreaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;

class DashboardController extends Controller
{
    /**
     * @var AreaService
     */
    private AreaService $areaService;

    /**
     * Create a new Controller instance.
     *
     * @param AreaService $areaService
     */
    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        if (is_null(auth()->user())) {
            return redirect()->route('login');
        }
        $role = auth()
            ->user()
            ->load('role')->role;
        if (!is_null($role) && $role->isOperator()) {
            return redirect()->route('stocks.create');
        }
        $area = $this->areaService->resolve($request);
        $areas = $this->areaService->collection();
        return inertia('Administrator/Dashboard/Index', [
            'area' => $area,
            'areaIds' => $areas->pluck('id')->toArray(),
            'areas' => $areas->pluck('name')->toArray(),
            'areaFinalSummaries' => [],
            'gapValueRank' => [],
        ]);
    }
}
