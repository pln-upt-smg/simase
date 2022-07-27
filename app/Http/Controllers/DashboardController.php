<?php

namespace App\Http\Controllers;

use App\Services\{
    AssetService,
    AssetTypeService,
    AssetSubmissionService,
    AssetLossDamageService,
    AssetTransferService,
    ProvinceService,
    CertificateService
};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * @var AssetService
     */
    private AssetService $assetService;

    /**
     * @var AssetTypeService
     */
    private AssetTypeService $assetTypeService;

    /**
     * @var AssetSubmissionService
     */
    private AssetSubmissionService $assetSubmissionService;

    /**
     * @var AssetLossDamageService
     */
    private AssetLossDamageService $assetLossDamageService;

    /**
     * @var AssetTransferService
     */
    private AssetTransferService $assetTransferService;

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
     * @param AssetService $assetService
     * @param AssetTypeService $assetTypeService
     * @param AssetSubmissionService $assetSubmissionService
     * @param AssetLossDamageService $assetLossDamageService
     * @param AssetTransferService $assetTransferService
     * @param ProvinceService $provinceService
     * @param CertificateService $certificateService
     */
    public function __construct(
        AssetService $assetService,
        AssetTypeService $assetTypeService,
        AssetSubmissionService $assetSubmissionService,
        AssetLossDamageService $assetLossDamageService,
        AssetTransferService $assetTransferService,
        ProvinceService $provinceService,
        CertificateService $certificateService
    ) {
        $this->assetService = $assetService;
        $this->assetTypeService = $assetTypeService;
        $this->assetSubmissionService = $assetSubmissionService;
        $this->assetLossDamageService = $assetLossDamageService;
        $this->assetTransferService = $assetTransferService;
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
        $assetTypes = $this->assetTypeService->collection();
        return inertia('Administrator/Dashboard/Index', [
            'provinceIds' => $provinces->pluck('id')->toArray(),
            'provinces' => $provinces->pluck('name')->toArray(),
            'assetTypeIds' => $assetTypes->pluck('id')->toArray(),
            'assetTypes' => $assetTypes->pluck('name')->toArray(),
            'TotalAssetChartData' => $this->assetService->assetTypeChart(),
            'RecentAssetSubmissionTableData' => $this->assetSubmissionService
                ->collection(null, true, 5)
                ->toArray(),
            'RecentAssetLossDamageTableData' => $this->assetLossDamageService
                ->collection(null, true, 5)
                ->toArray(),
            'RecentAssetTransferTableData' => $this->assetTransferService
                ->collection(null, true, 5)
                ->toArray(),
            'KKUCertificateChartData' => $this->certificateService->provinceChart(),
            'KKURecentCertificateTableData' => $this->certificateService
                ->collection(null, true)
                ->toArray(),
        ]);
    }
}
