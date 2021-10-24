<?php

Route::group([
    'middleware' => ['authorizable.administrator']
], static function () {

    /**
     * Operators endpoints
     */
    include 'Endpoints/operators.php';

});
