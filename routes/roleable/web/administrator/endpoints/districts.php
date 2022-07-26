<?php

use App\Http\Controllers\Administrator\DistrictController;

Route::resource('districts', DistrictController::class)->except([
    'create',
    'show',
    'edit',
]);

Route::group(
    [
        'prefix' => 'districts',
        'as' => 'districts.',
    ],
    static function () {
        Route::post('import', [DistrictController::class, 'import'])->name(
            'import'
        );

        Route::get('export', [DistrictController::class, 'export'])->name(
            'export'
        );
    }
);
