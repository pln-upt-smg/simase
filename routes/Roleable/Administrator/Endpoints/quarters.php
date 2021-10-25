<?php

use App\Http\Controllers\Administrator\QuarterController;

Route::resource('quarters', QuarterController::class)->except(['create', 'show', 'edit']);
