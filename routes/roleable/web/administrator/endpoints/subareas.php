<?php

use App\Http\Controllers\Administrator\SubAreaController;

Route::resource('subareas', SubAreaController::class)->except(['create', 'show', 'edit']);

Route::group([
    'prefix' => 'subareas',
    'as' => 'subareas.'
], static function () {

    Route::post('import', [SubAreaController::class, 'import'])->name('import');

    Route::get('export', [SubAreaController::class, 'export'])->name('export');

});
