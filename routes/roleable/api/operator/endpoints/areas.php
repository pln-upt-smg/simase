<?php

use App\Http\Controllers\Administrator\AreaController;

Route::get('area', [AreaController::class, 'json'])->name('area');

Route::get('areas', [AreaController::class, 'jsonCollection'])->name('areas');
