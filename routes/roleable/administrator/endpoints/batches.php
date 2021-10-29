<?php

use App\Http\Controllers\Administrator\BatchController;

Route::resource('batches', BatchController::class)->except(['create', 'show', 'edit']);

Route::group([
    'prefix' => 'batches',
    'as' => 'batches.'
], static function () {

    Route::post('import', [BatchController::class, 'import'])->name('import');

    Route::get('export', [BatchController::class, 'export'])->name('export');

});
