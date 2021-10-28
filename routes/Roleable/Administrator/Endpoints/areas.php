<?php

use App\Http\Controllers\Administrator\AreaController;

Route::resource('areas', AreaController::class)->except(['create', 'show', 'edit']);

Route::group([
    'prefix' => 'areas',
    'as' => 'areas'
], static function () {

    Route::post('import', [AreaController::class, 'import'])->name('import');

    Route::get('export', [AreaController::class, 'export'])->name('export');

});
