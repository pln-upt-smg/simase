<?php

use App\Http\Controllers\Administrator\FinalSummaryController;

Route::resource('summaries', FinalSummaryController::class)->only('index');

Route::group([
    'prefix' => 'summaries',
    'as' => 'summaries.'
], static function () {

    Route::get('export', [FinalSummaryController::class, 'export'])->name('export');

});
