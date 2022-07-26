<?php

use App\Http\Controllers\Administrator\ProvinceController;

Route::resource('provinces', ProvinceController::class)->except([
    'create',
    'show',
    'edit',
]);

Route::group(
    [
        'prefix' => 'provinces',
        'as' => 'provinces.',
    ],
    static function () {
        Route::post('import', [ProvinceController::class, 'import'])->name(
            'import'
        );

        Route::get('export', [ProvinceController::class, 'export'])->name(
            'export'
        );
    }
);
