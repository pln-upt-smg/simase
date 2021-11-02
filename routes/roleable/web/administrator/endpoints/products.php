<?php

use App\Http\Controllers\Administrator\ProductController;
use App\Http\Controllers\Administrator\ProductMaterialController;

Route::resource('products', ProductController::class)->except(['create', 'show', 'edit']);

Route::group([
    'prefix' => 'products',
    'as' => 'products.'
], static function () {

    Route::post('import', [ProductController::class, 'import'])->name('import');

    Route::get('export', [ProductController::class, 'export'])->name('export');

    Route::resource('materials', ProductMaterialController::class)->except(['create', 'show', 'edit']);

    Route::group([
        'prefix' => 'materials',
        'as' => 'materials.'
    ], static function () {

        Route::post('import', [ProductMaterialController::class, 'import'])->name('import');

        Route::get('export', [ProductMaterialController::class, 'export'])->name('export');

    });

});
