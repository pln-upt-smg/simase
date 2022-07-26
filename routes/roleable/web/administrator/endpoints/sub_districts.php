<?php

use App\Http\Controllers\Administrator\SubDistrictController;

Route::resource('sub-districts', SubDistrictController::class)->except([
    'create',
    'show',
    'edit',
]);

Route::group(
    [
        'prefix' => 'sub-districts',
        'as' => 'sub-districts.',
    ],
    static function () {
        Route::post('import', [SubDistrictController::class, 'import'])->name(
            'import'
        );

        Route::get('export', [SubDistrictController::class, 'export'])->name(
            'export'
        );
    }
);
