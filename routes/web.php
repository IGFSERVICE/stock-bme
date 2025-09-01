<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::post('/stock/rupture', [StockController::class, 'markRupture'])->name('stock.rupture');
Route::get('/stock/rupturesActuelExport', [StockController::class, 'RupturesActuelExport'])->name('stock.rupturesActuelExport');
Route::get('/stock/export', [StockController::class, 'export'])->name('stock.export');
Route::get('/ruptures/export', [StockController::class, 'exportRuptures'])->name('stock.exportRuptures');
Route::get('/ruptures', [StockController::class, 'ruptures'])->name('stock.ruptures');
Route::get('/stock/{ref}', [StockController::class, 'show'])->name('stock.show');
Route::get('/ruptures-actuel', [StockController::class, 'rupturesActuel'])->name('stock.rupturesActuel');
Route::get('/mboup', [StockController::class, 'indexMboup'])->name('stock.index');


