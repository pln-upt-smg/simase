<?php

use App\Http\Controllers\Administrator\MaterialController;

Route::get('material', [MaterialController::class, 'json'])->name('material');

Route::get('materials', [MaterialController::class, 'jsonCollection'])->name('materials');
