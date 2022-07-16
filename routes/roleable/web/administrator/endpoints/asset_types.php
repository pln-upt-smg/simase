<?php

use App\Http\Controllers\Administrator\AssetTypeController;

Route::group(
    [
        'prefix' => 'assets',
        'as' => 'assets.',
    ],
    static function () {
        Route::resource('types', AssetTypeController::class)->except([
            'create',
            'show',
            'edit',
        ]);

        Route::group(
            [
                'prefix' => 'types',
                'as' => 'types.',
            ],
            static function () {
                Route::post('import', [
                    AssetTypeController::class,
                    'import',
                ])->name('import');

                Route::get('export', [
                    AssetTypeController::class,
                    'export',
                ])->name('export');
            }
        );
    }
);
