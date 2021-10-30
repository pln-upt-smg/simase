<?php

use App\Http\Controllers\Administrator\PidController;
use App\Http\Controllers\Administrator\PidDetailController;

Route::resource('pids', PidController::class)->only('index');

Route::group([
    'prefix' => 'pids',
    'as' => 'pids.'
], static function () {

    Route::get('export', [PidController::class, 'export'])->name('export');

    Route::resource('details', PidDetailController::class)->only('index');

    Route::group([
        'prefix' => 'details',
        'as' => 'details.'
    ], static function () {

        Route::get('export', [PidDetailController::class, 'export'])->name('export');

    });

});
