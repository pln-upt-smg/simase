<?php

use App\Http\Controllers\Administrator\CertificateController;

Route::resource('certificates', CertificateController::class)->except([
    'create',
    'show',
    'edit',
]);

Route::group(
    [
        'prefix' => 'certificates',
        'as' => 'certificates.',
    ],
    static function () {
        Route::post('import', [CertificateController::class, 'import'])->name(
            'import'
        );

        Route::get('export', [CertificateController::class, 'export'])->name(
            'export'
        );
    }
);
