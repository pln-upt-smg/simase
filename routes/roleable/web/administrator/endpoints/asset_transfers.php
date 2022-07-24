<?php

use App\Http\Controllers\Administrator\AssetTransferController;

Route::group(
    [
        'prefix' => 'assets',
        'as' => 'assets.',
    ],
    static function () {
        Route::resource('transfers', AssetTransferController::class)->except([
            'create',
            'show',
            'edit',
        ]);

        Route::group(
            [
                'prefix' => 'transfers',
                'as' => 'transfers.',
            ],
            static function () {
                Route::post('import', [
                    AssetTransferController::class,
                    'import',
                ])->name('import');

                Route::get('export', [
                    AssetTransferController::class,
                    'export',
                ])->name('export');
            }
        );
    }
);
