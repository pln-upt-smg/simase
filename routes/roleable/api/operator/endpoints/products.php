<?php

use App\Http\Controllers\Administrator\ProductController;

Route::group([
    'prefix' => 'products',
    'as' => 'api.products.'
], static function () {

    Route::get('code', [ProductController::class, 'productJson'])->name('code');

    Route::get('codes', [ProductController::class, 'productCodeJsonCollection'])->name('codes');

});
