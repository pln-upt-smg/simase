<?php

Route::group([
    'middleware' => ['authorizable.administrator']
], static function () {

    /**
     * Periods endpoints
     */
    include 'Endpoints/periods.php';

    /**
     * Areas endpoints
     */
    include 'Endpoints/areas.php';

    /**
     * Materials endpoints
     */
    include 'Endpoints/materials.php';

    /**
     * Stocks endpoints
     */
    include 'Endpoints/stocks.php';

    /**
     * Operators endpoints
     */
    include 'Endpoints/operators.php';

});
