<?php

use App\Http\Controllers\Administrator\BatchController;

Route::get('batch', [BatchController::class, 'json'])->name('batch');

Route::get('batches', [BatchController::class, 'jsonCollection'])->name('batches');
