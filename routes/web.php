<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::post('/stock/rupture', [StockController::class, 'markRupture'])->name('stock.rupture');
Route::get('/stock/export', [StockController::class, 'export'])->name('stock.export');
Route::get('/ruptures/export', [StockController::class, 'exportRuptures'])->name('stock.exportRuptures');
Route::get('/ruptures', [StockController::class, 'ruptures'])->name('stock.ruptures');

