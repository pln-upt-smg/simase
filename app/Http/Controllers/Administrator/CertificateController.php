<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\{
    CertificateService,
    UrbanVillageService,
    SubDistrictService,
    DistrictService,
    ProvinceService,
    HolderService,
    OptionalityService
};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CertificateController extends Controller
{
    /**
     * @var CertificateService
     */
    private CertificateService $certificateService;

    /**
     * @var UrbanVillageService
     */
    private UrbanVillageService $urbanVillageService;

    /**
     * @var SubDistrictService
     */
    private SubDistrictService $subDistrictService;

    /**
     * @var DistrictService
     */
    private DistrictService $districtService;

    /**
     * @var ProvinceService
     */
    private ProvinceService $provinceService;

    /**
     * @var HolderService
     */
    private HolderService $holderService;

    /**
     * @var OptionalityService
     */
    private OptionalityService $optionalityService;

    /**
     * Create a new Controller instance.
     *
     * @param CertificateService $certificateService
     * @param UrbanVillageService $urbanVillageService
     * @param SubDistrictService $subDistrictService
     * @param DistrictService $districtService
     * @param ProvinceService $provinceService
     * @param HolderService $holderService
     * @param OptionalityService $optionalityService
     */
    public function __construct(
        CertificateService $certificateService,
        UrbanVillageService $urbanVillageService,
        SubDistrictService $subDistrictService,
        DistrictService $districtService,
        ProvinceService $provinceService,
        HolderService $holderService,
        OptionalityService $optionalityService
    ) {
        $this->certificateService = $certificateService;
        $this->urbanVillageService = $urbanVillageService;
        $this->subDistrictService = $subDistrictService;
        $this->districtService = $districtService;
        $this->provinceService = $provinceService;
        $this->holderService = $holderService;
        $this->optionalityService = $optionalityService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return inertia('Administrator/Certificates/Index', [
            'certificates' => $this->certificateService->tableData(),
            'urban_villages' => $this->urbanVillageService
                ->collection()
                ->toArray(),
            'sub_districts' => $this->subDistrictService
                ->collection()
                ->toArray(),
            'districts' => $this->districtService->collection()->toArray(),
            'provinces' => $this->provinceService->collection()->toArray(),
            'holders' => $this->holderService->collection()->toArray(),
            'optionalities' => $this->optionalityService
                ->collection()
                ->toArray(),
            'template' => $this->certificateService->template(),
        ])->table(function (InertiaTable $table) {
            $this->certificateService->tableMeta($table);
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
        $this->certificateService->store($request);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Certificate $certificate
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(
        Request $request,
        Certificate $certificate
    ): RedirectResponse {
        $this->certificateService->update($request, $certificate);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Certificate $certificate
     * @return RedirectResponse
     * @throws Throwable
     */
    public function destroy(Certificate $certificate): RedirectResponse
    {
        $this->certificateService->destroy($certificate);
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
        $this->certificateService->import($request);
        return back();
    }

    /**
     * Export the resource to specified file.
     *
     * @return BinaryFileResponse
     * @throws Throwable
     */
    public function export(): BinaryFileResponse
    {
        return $this->certificateService->export();
    }

    /**
     * Generate the JSON-formatted resource data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = $this->certificateService->single($request);
        if (!is_null($data)) {
            $data = $data->toJson();
        }
        return response()->json($data);
    }

    /**
     * Generate the JSON-formatted resource collection data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonCollection(Request $request): JsonResponse
    {
        $data = $this->certificateService->collection($request);
        return response()->json([
            'items' => $data->toArray(),
            'total_count' => $data->count(),
        ]);
    }
}
