<?php

namespace App\Http\Controllers\Administrator;

use App\Exports\AreasExport;
use App\Http\Controllers\Controller;
use App\Http\Helper\MediaHelper;
use App\Imports\AreasImport;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|ResponseFactory
     */
    public function index(): \Inertia\Response|ResponseFactory
    {
        $data = QueryBuilder::for(Area::class)
            ->select([
                'areas.id as id',
                'areas.name as name'
            ])
            ->defaultSort('name')
            ->allowedSorts(['name'])
            ->paginate()
            ->withQueryString();
        return inertia('Administrator/Areas/Index', [
            'areas' => $data,
            'template' => $this->template()
        ])->table(function (InertiaTable $table) {
            $table->addSearchRows([
                'name' => 'Nama Area'
            ])->addColumns([
                'name' => 'Nama Area',
                'action' => 'Aksi'
            ]);
        });
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function store(Request $request): Response|RedirectResponse
    {
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:areas']
        ])->validate();
        Area::create([
            'name' => Str::title($request->name)
        ]);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Area $area
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Area $area): Response|RedirectResponse
    {
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('areas')->ignore($area->id)]
        ])->validate();
        $area->updateOrFail([
            'name' => Str::title($request->name)
        ]);
        $area->save();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Area $area
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function destroy(Area $area): Response|RedirectResponse
    {
        $area->deleteOrFail();
        return back();
    }

    /**
     * Import the resource from file.
     *
     * @param Request $request
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function import(Request $request): Response|RedirectResponse
    {
        MediaHelper::importSpreadsheet($request, new AreasImport);
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
        return MediaHelper::exportSpreadsheet(new AreasExport, new Area);
    }

    /**
     * File URL for the resource import template
     *
     * @return string
     */
    public function template(): string
    {
        return 'https://docs.google.com/spreadsheets/d/1_iyLqpZbz09w22YRenD7kFuyidQJIUSf4-33jkZ8_kA/edit?usp=sharing';
    }
}
