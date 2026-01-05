<?php

use App\Http\Controllers\Tenant\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;


Route::group(["prefix" => "dashboard"], function () {

    Route::group(["prefix" => "dashboard"], function () {
        Route::get('index', [DashboardController::class, 'index'])->name('tenant.dashboard.dashboard.index');
        Route::get('/getData', [DashboardController::class, 'getData'])->name('tenant.dashboard.dashboard.getData');
        Route::get('/getStockMin', [DashboardController::class, 'getStockMin'])->name('tenant.dashboard.dashboard.getStockMin');
        Route::get('/excelProductosStockMin', [DashboardController::class, 'excelProductosStockMin'])->name('tenant.dashboard.dashboard.excelProductosStockMin');
    });
});
