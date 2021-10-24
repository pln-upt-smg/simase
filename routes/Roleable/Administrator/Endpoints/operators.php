<?php

use App\Http\Controllers\Administrator\OperatorController;

Route::resource('operators', OperatorController::class)->except(['create', 'show', 'edit']);

Route::group([
    'prefix' => 'operators'
], static function () {

    Route::post('import', [OperatorController::class, 'import'])->name('operators.import');

    Route::get('export', [OperatorController::class, 'export'])->name('operators.export');

});
