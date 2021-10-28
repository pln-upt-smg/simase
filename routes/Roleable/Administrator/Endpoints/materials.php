<?php

use App\Http\Controllers\Administrator\MaterialController;

Route::resource('materials', MaterialController::class)->except(['create', 'show', 'edit']);

Route::group([
    'prefix' => 'materials'
], static function () {

    Route::post('import', [MaterialController::class, 'import'])->name('materials.import');

    Route::get('export', [MaterialController::class, 'export'])->name('materials.export');

});
