<?php

use App\Http\Controllers\Administrator\AssetController;

Route::get('asset', [AssetController::class, 'json'])->name('asset');

Route::get('assets', [AssetController::class, 'jsonCollection'])->name(
    'assets'
);
