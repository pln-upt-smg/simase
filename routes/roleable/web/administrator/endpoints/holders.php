<?php

use App\Http\Controllers\Administrator\HolderController;

Route::resource('holders', HolderController::class)->except([
    'create',
    'show',
    'edit',
]);

Route::group(
    [
        'prefix' => 'holders',
        'as' => 'holders.',
    ],
    static function () {
        Route::post('import', [HolderController::class, 'import'])->name(
            'import'
        );

        Route::get('export', [HolderController::class, 'export'])->name(
            'export'
        );
    }
);
