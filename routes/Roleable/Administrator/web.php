<?php

use App\Http\Controllers\Administrator\OperatorController;

Route::get('operators', [OperatorController::class, 'index'])->name('operators.index');
