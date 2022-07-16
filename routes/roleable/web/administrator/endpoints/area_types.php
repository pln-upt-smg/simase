<?php

use App\Http\Controllers\Administrator\AreaTypeController;

Route::group(
    [
        'prefix' => 'areas',
        'as' => 'areas.',
    ],
    static function () {
        Route::resource('types', AreaTypeController::class)->except([
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
                    AreaTypeController::class,
                    'import',
                ])->name('import');

                Route::get('export', [
                    AreaTypeController::class,
                    'export',
                ])->name('export');
            }
        );
    }
);
