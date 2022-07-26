<?php

namespace App\Http\Controllers;

use App\Services\{ProvinceService, CertificateService};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * @var ProvinceService
     */
    private ProvinceService $provinceService;

    /**
     * @var CertificateService
     */
    private CertificateService $certificateService;

    /**
     * Create a new Controller instance.
     *
     * @param ProvinceService $provinceService
     */
    public function __construct(
        ProvinceService $provinceService,
        CertificateService $certificateService
    ) {
        $this->provinceService = $provinceService;
        $this->certificateService = $certificateService;
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
        $provinces = $this->provinceService->collection();
        return inertia('Administrator/Dashboard/Index', [
            'provinceIds' => $provinces->pluck('id')->toArray(),
            'provinces' => $provinces->pluck('name')->toArray(),
            'KKUCertificateChartData' => $this->certificateService->provinceChart(),
            'KKURecentCertificateTableData' => $this->certificateService
                ->collection(null, true)
                ->toArray(),
        ]);
    }
}
