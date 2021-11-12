<?php

use App\Http\Controllers\Administrator\ProductController;

Route::get('product', [ProductController::class, 'json'])->name('product');

Route::get('products', [ProductController::class, 'jsonCollection'])->name('products');
