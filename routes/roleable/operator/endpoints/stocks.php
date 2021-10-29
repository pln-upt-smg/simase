<?php

use App\Http\Controllers\Operator\ActualStockController;

Route::resource('stocks', ActualStockController::class)->except(['show', 'edit']);
