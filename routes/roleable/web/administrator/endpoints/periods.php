<?php

use App\Http\Controllers\Administrator\PeriodController;

Route::resource('periods', PeriodController::class)->except(['create', 'show', 'edit']);
