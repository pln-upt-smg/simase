<?php

Route::group([
    'middleware' => ['auth:sanctum', 'verified']
], static function () {

    /**
     * Administrator api routes
     */
    Route::group([
        'middleware' => ['authorizable.operator']
    ], static function () {

        include 'operator/api.php';

    });

});
