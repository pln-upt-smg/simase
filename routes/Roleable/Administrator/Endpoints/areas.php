<?php

use App\Http\Controllers\Administrator\AreaController;

Route::resource('areas', AreaController::class)->except(['create', 'show', 'edit']);

Route::group([
    'prefix' => 'areas'
], static function () {

    Route::post('import', [AreaController::class, 'import'])->name('areas.import');

    Route::get('export', [AreaController::class, 'export'])->name('areas.export');

});
