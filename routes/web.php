<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\VehiclePrintSheetController;
use App\Http\Controllers\Public\LeadController;
use App\Http\Controllers\Public\VehicleCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VehicleCatalogController::class, 'index'])->name('catalog.index');
Route::get('/veiculos/{slug}', [VehicleCatalogController::class, 'show'])->name('catalog.show');
Route::post('/veiculos/{slug}/proposta', [LeadController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('catalog.leads.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/admin/veiculos/{vehicle}/ficha-patio', VehiclePrintSheetController::class)
        ->name('admin.vehicles.print-sheet');
});
