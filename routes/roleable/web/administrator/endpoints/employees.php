<?php

use App\Http\Controllers\Administrator\EmployeeController;

Route::resource('employees', EmployeeController::class)->except([
    'create',
    'show',
    'edit',
]);

Route::group(
    [
        'prefix' => 'employees',
        'as' => 'employees.',
    ],
    static function () {
        Route::post('import', [EmployeeController::class, 'import'])->name(
            'import'
        );

        Route::get('export', [EmployeeController::class, 'export'])->name(
            'export'
        );
    }
);
