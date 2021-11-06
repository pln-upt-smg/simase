<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', static function () {
    return redirect()->route('login');
});

if (app()->isLocal() || config('app.env_staging')) {

    Route::get('phpinfo', static function () {
        /** @noinspection ForgottenDebugOutputInspection */
        return phpinfo();
    });

}

/**
 * Role-specific web routes
 */
include "roleable/web/web.php";
