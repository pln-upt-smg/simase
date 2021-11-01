<?php

use App\Http\Controllers\DashboardController;

Route::group([
    'middleware' => ['auth:sanctum', 'verified']
], static function () {

    /**
     * Role-specific initial redirection
     */
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /**
     * Administrator web routes
     */
    Route::group([
        'middleware' => ['authorizable.administrator']
    ], static function () {

        include 'administrator/web.php';

    });

    /**
     * Operator web routes
     */
    Route::group([
        'middleware' => ['authorizable.operator']
    ], static function () {

        include 'operator/web.php';

    });

});
