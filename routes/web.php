<?php

declare(strict_types=1);

use App\Http\Controllers\Public\VehicleCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VehicleCatalogController::class, 'index'])->name('catalog.index');
Route::get('/veiculos/{slug}', [VehicleCatalogController::class, 'show'])->name('catalog.show');
