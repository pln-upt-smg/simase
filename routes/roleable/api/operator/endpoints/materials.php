<?php

use App\Http\Controllers\Administrator\MaterialController;

Route::group([
    'prefix' => 'materials',
    'as' => 'api.materials.'
], static function () {

    Route::get('code', [MaterialController::class, 'materialJson'])->name('code');

    Route::get('codes', [MaterialController::class, 'materialCodeJsonCollection'])->name('codes');

});
