<?php

use App\Http\Controllers\Administrator\ActualStockController;
use App\Http\Controllers\Administrator\BookStockController;

Route::group([
    'prefix' => 'stocks',
    'as' => 'stocks.'
], static function () {

    Route::group([
        'prefix' => 'actuals',
        'as' => 'actuals.'
    ], static function () {

        Route::post('import', [ActualStockController::class, 'import'])->name('import');

        Route::get('export', [ActualStockController::class, 'export'])->name('export');

    });

	Route::resource('actuals', ActualStockController::class)->except(['create', 'show', 'edit']);

    Route::group([
        'prefix' => 'books',
        'as' => 'books.'
    ], static function () {

        Route::post('import', [BookStockController::class, 'import'])->name('import');

        Route::get('export', [BookStockController::class, 'export'])->name('export');

	    Route::delete('truncate', [BookStockController::class, 'truncate'])->name('truncate');

    });

	Route::resource('books', BookStockController::class)->except(['create', 'show', 'edit']);

});
