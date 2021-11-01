<?php

use App\Http\Controllers\Administrator\BatchNotExistController;

Route::resource('batch-not-exists', BatchNotExistController::class)->only('index');

Route::group([
    'prefix' => 'batch-not-exists',
    'as' => 'batch-not-exists.'
], static function () {

    Route::get('export', [BatchNotExistController::class, 'export'])->name('export');

});
