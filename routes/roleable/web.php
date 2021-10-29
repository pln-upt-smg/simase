<?php

Route::group([
    'middleware' => ['auth:sanctum', 'verified']
], static function () {

    /**
     * Role-specific initial redirection
     */
    Route::get('dashboard', static function () {
        if (is_null(auth()->user())) {
            return redirect()->route('login');
        }
        if (auth()->user()?->load('role')->role->isOperator()) {
            return redirect()->route('stocks.create');
        }
        return inertia('Administrator/Dashboard');
    })->name('dashboard');

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
