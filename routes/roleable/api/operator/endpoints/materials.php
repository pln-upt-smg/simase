<?php

use App\Http\Controllers\Administrator\MaterialController;

Route::group([
    'prefix' => 'materials',
    'as' => 'api.materials.'
], static function () {

    Route::get('codes', [MaterialController::class, 'materialCodeJsonCollection'])->name('codes');

});
