<?php

use App\Http\Controllers\Administrator\MaterialController;

Route::resource('materials', MaterialController::class)->except(['create', 'show', 'edit']);

Route::group([
    'prefix' => 'materials',
    'as' => 'materials'
], static function () {

    Route::post('import', [MaterialController::class, 'import'])->name('import');

    Route::get('export', [MaterialController::class, 'export'])->name('export');

});
