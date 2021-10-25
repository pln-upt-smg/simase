<?php

Route::group([
    'middleware' => ['authorizable.administrator']
], static function () {

    /**
     * Qaurters endpoints
     */
    include 'Endpoints/quarters.php';

    /**
     * Areas endpoints
     */
    include 'Endpoints/areas.php';

    /**
     * Operators endpoints
     */
    include 'Endpoints/operators.php';

});
