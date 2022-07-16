<?php

use App\Http\Controllers\Administrator\AssetSubmissionController;

Route::group(
    [
        'prefix' => 'assets',
        'as' => 'assets.',
    ],
    static function () {
        Route::resource(
            'submissions',
            AssetSubmissionController::class
        )->except(['create', 'show', 'edit']);

        Route::group(
            [
                'prefix' => 'submissions',
                'as' => 'submissions.',
            ],
            static function () {
                Route::post('import', [
                    AssetSubmissionController::class,
                    'import',
                ])->name('import');

                Route::get('export', [
                    AssetSubmissionController::class,
                    'export',
                ])->name('export');
            }
        );
    }
);
