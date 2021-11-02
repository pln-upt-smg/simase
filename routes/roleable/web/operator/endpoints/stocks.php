<?php

use App\Http\Controllers\Operator\ActualStockController;

Route::resource('stocks', ActualStockController::class)->except(['show', 'edit']);

Route::group([
    'prefix' => 'stocks',
    'as' => 'stocks.'
], static function () {

    Route::group([
        'prefix' => 'sku',
        'as' => 'sku.'
    ], static function () {

        Route::get('create', [ActualStockController::class, 'createSku'])->name('create');

    });

});
