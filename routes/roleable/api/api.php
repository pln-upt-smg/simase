<?php

Route::group(
    [
        'middleware' => ['auth:sanctum', 'verified'],
    ],
    static function () {
        /**
         * Administrator api routes
         */
        Route::group(
            [
                'middleware' => ['authorizable.administrator'],
            ],
            static function () {
                include 'administrator/api.php';
            }
        );

        /**
         * Operator api routes
         */
        Route::group(
            [
                'middleware' => ['authorizable.operator'],
            ],
            static function () {
                include 'operator/api.php';
            }
        );
    }
);
