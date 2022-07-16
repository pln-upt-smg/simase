<?php

use App\Http\Controllers\Administrator\AssetController;

Route::resource('assets', AssetController::class)->except([
    'create',
    'show',
    'edit',
]);

Route::group(
    [
        'prefix' => 'assets',
        'as' => 'assets.',
    ],
    static function () {
        Route::post('import', [AssetController::class, 'import'])->name(
            'import'
        );

        Route::get('export', [AssetController::class, 'export'])->name('export');
    }
);
