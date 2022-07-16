<?php

use App\Http\Controllers\Administrator\AssetLossDamageController;

Route::group(
    [
        'prefix' => 'assets',
        'as' => 'assets.',
    ],
    static function () {
        Route::resource('loss', AssetLossDamageController::class)->except([
            'create',
            'show',
            'edit',
        ]);

        Route::group(
            [
                'prefix' => 'loss',
                'as' => 'loss.',
            ],
            static function () {
                Route::post('import', [
                    AssetLossDamageController::class,
                    'import',
                ])->name('import');

                Route::get('export', [
                    AssetLossDamageController::class,
                    'export',
                ])->name('export');
            }
        );
    }
);
