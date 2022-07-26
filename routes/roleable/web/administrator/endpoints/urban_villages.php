<?php

use App\Http\Controllers\Administrator\UrbanVillageController;

Route::resource('urban-villages', UrbanVillageController::class)->except([
    'create',
    'show',
    'edit',
]);

Route::group(
    [
        'prefix' => 'urban-villages',
        'as' => 'urban-villages.',
    ],
    static function () {
        Route::post('import', [UrbanVillageController::class, 'import'])->name(
            'import'
        );

        Route::get('export', [UrbanVillageController::class, 'export'])->name(
            'export'
        );
    }
);
