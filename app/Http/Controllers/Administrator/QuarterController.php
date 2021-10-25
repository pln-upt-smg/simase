<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Quarter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\ResponseFactory;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class QuarterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|ResponseFactory
     */
    public function index(): \Inertia\Response|ResponseFactory
    {
        $data = QueryBuilder::for(Quarter::class)
            ->select([
                'quarters.id as id',
                'quarters.name as name'
            ])
            ->defaultSort('name')
            ->allowedSorts(['name'])
            ->paginate()
            ->withQueryString();
        return inertia('Administrator/Quarters/Index', [
            'quarters' => $data
        ])->table(function (InertiaTable $table) {
            $table->addSearchRows([
                'name' => 'Nama Quarter'
            ])->addColumns([
                'name' => 'Nama Quarter',
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
            'name' => ['required', 'string', 'max:255', 'unique:quarters']
        ])->validate();
        Quarter::create([
            'name' => Str::title($request->name)
        ]);
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Quarter $quarter
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function update(Request $request, Quarter $quarter): Response|RedirectResponse
    {
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('quarters')->ignore($quarter->id)]
        ])->validate();
        $quarter->updateOrFail([
            'name' => Str::title($request->name)
        ]);
        $quarter->save();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Quarter $quarter
     * @return Response|RedirectResponse
     * @throws Throwable
     */
    public function destroy(Quarter $quarter): Response|RedirectResponse
    {
        $quarter->deleteOrFail();
        return back();
    }
}
