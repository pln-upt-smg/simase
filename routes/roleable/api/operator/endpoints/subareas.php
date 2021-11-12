<?php

use App\Http\Controllers\Administrator\SubAreaController;

Route::get('subarea', [SubAreaController::class, 'json'])->name('subarea');

Route::get('subareas', [SubAreaController::class, 'jsonCollection'])->name('subareas');
